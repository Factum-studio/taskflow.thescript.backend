<?php

declare(strict_types=1);

namespace modules\passport\auth\application\handler;

use core\application\command\SyncUserCommand;
use core\application\handler\SyncUserHandler;
use core\domain\exception\ValidationException;
use modules\passport\auth\application\command\HandleSsoCallbackCommand;
use modules\passport\auth\application\dto\PassportTokenDto;
use modules\passport\auth\application\port\PassportAuthPort;
use RuntimeException;
use Throwable;

final class HandleSsoCallbackHandler
{
    public function __construct(
        private readonly PassportAuthPort $passport,
        private readonly SyncUserHandler $syncHandler,
    ) {
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function handle(HandleSsoCallbackCommand $command): PassportTokenDto
    {
        if ($command->code === '') {
            throw new RuntimeException('Authorization code is required');
        }

        if (!hash_equals($command->expectedState, $command->state)) {
            throw new RuntimeException('Invalid OAuth state');
        }

        $tokens = $this->passport->exchangeAuthorizationCode($command->code);

        $claims = $this->passportClaims($tokens->accessToken);
        $sub = $claims['sub'] ?? null;

        if (!is_string($sub) && !is_int($sub)) {
            throw new RuntimeException('Passport token does not contain a user subject');
        }

        $user = $this->passport->getUser((string)$sub, $tokens->accessToken);
        $this->syncHandler->handle(new SyncUserCommand(
            passportId: (int)$user->id,
            surname: $user->surname,
            name: $user->name,
            email: $user->email,
            isOwner: $user->isOwner,
            patronymic: $user->patronymic,
            post: $user->post,
        ));

        return $tokens;
    }

    /**
     * We intentionally only decode the payload here. Signature validation is
     * performed by PassportAuthMiddleware for normal API requests. The callback
     * token is still received directly from Passport over HTTPS and is used
     * immediately to fetch the user.
     *
     * @return array<string, mixed>
     */
    private function passportClaims(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid access token');
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/') . str_repeat('=', (4 - strlen($parts[1]) % 4) % 4), true);
        if ($payload === false) {
            throw new RuntimeException('Invalid access token payload');
        }

        $claims = json_decode($payload, true);
        if (!is_array($claims)) {
            throw new RuntimeException('Invalid access token claims');
        }

        return $claims;
    }
}
