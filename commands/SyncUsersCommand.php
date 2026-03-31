<?php

namespace app\commands;

use core\application\port\ILocalUserRepository;
use core\application\port\IPassportGateway;
use core\domain\valueObject\JwtToken;
use core\domain\valueObject\QueryParams;
use modules\projects\application\command\CreateProjectCommand;
use modules\projects\application\handler\CreateProjectHandler;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class SyncUsersCommand extends Controller
{
    private IPassportGateway $passportGateway;
    private ILocalUserRepository $localUserRepository;
    private CreateProjectHandler $createProjectHandler;
    private JwtToken $systemToken;

    public function __construct(
        $id,
        $module,
        IPassportGateway $passportGateway,
        ILocalUserRepository $localUserRepository,
        CreateProjectHandler $createProjectHandler,
        $config = []
    ) {
        $this->passportGateway = $passportGateway;
        $this->localUserRepository = $localUserRepository;
        $this->createProjectHandler = $createProjectHandler;

        $token = $_ENV['USER_SERVICE_TOKEN'] ?? '';
        if (empty($token)) {
            throw new \InvalidArgumentException('USER_SERVICE_TOKEN environment variable is not set');
        }
        $this->systemToken = new JwtToken($token);

        parent::__construct($id, $module, $config);
    }

    public function actionIndex(int $limit = 100, bool $dryRun = false, string $expand = 'contacts,post'): int
    {
        $this->stdout("Starting user sync from Passport...\n", Console::FG_YELLOW);

        $page = 1;
        $created = 0;
        $alreadyExists = 0;
        $errors = 0;

        do {
            $params = QueryParams::create(
                limit: $limit,
                page: $page,
                expand: explode(',', $expand)
            );

            $result = $this->passportGateway->getAllUsers($this->systemToken, $params);
            $users = $result['items'] ?? [];
            $total = $result['total'] ?? 0;

            if (empty($users)) {
                break;
            }

            foreach ($users as $userData) {
                $userId = (int)($userData['id'] ?? 0);
                if ($userId <= 0) {
                    $this->stderr("Skipping user with invalid ID\n", Console::FG_RED);
                    $errors++;
                    continue;
                }

                if ($this->localUserRepository->exists($userId)) {
                    $alreadyExists++;
                    continue;
                }

                $email = null;
                if (isset($userData['contacts']) && is_array($userData['contacts'])) {
                    foreach ($userData['contacts'] as $contact) {
                        if (($contact['name'] ?? '') === 'email') {
                            $email = $contact['data'] ?? null;
                            break;
                        }
                    }
                }

                if ($dryRun) {
                    $this->stdout("[DRY RUN] Would create user ID {$userId}, email: {$email}\n", Console::FG_CYAN);
                    $created++;
                    continue;
                }

                try {
                    // Создаём локальную запись пользователя
                    $this->localUserRepository->create($userId, $email ?? '');

                    // Создаём персональный проект напрямую
                    $command = new CreateProjectCommand(
                        name: 'Личный проект пользователя #' . $userId,
                        type: 'personal',
                        ownerId: $userId,
                        settings: []
                    );
                    $this->createProjectHandler->handle($command);

                    $this->stdout("Created user ID {$userId} with personal project\n", Console::FG_GREEN);
                    $created++;
                } catch (\Throwable $e) {
                    $this->stderr("Error creating user {$userId}: " . $e->getMessage() . "\n", Console::FG_RED);
                    $errors++;
                }
            }

            $this->stdout("Processed page {$page}, got " . count($users) . " users\n");
            $page++;
        } while (($page - 1) * $limit < $total);

        $this->stdout("\nSync completed.\n", Console::FG_GREEN);
        $this->stdout("Created: {$created}\n", Console::FG_GREEN);
        $this->stdout("Already existed: {$alreadyExists}\n", Console::FG_CYAN);
        $this->stdout("Errors: {$errors}\n", $errors > 0 ? Console::FG_RED : Console::FG_GREEN);

        return ExitCode::OK;
    }
}