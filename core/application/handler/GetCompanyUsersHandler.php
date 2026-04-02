<?php
namespace core\application\handler;

use core\application\dto\CompanyUserDto;
use core\application\port\ICompanyRepository;
use core\application\query\GetCompanyUsersQuery;
use core\domain\valueObject\CompanyId;
use core\domain\valueObject\QueryParams;
use core\infrastructure\persistence\UserAR;
use core\application\port\IPassportGateway;
use core\domain\valueObject\JwtToken;
use RuntimeException;
use Yii;

class GetCompanyUsersHandler
{
    public function __construct(
        private ICompanyRepository $companyRepository,
        private IPassportGateway $passportGateway
    ) {}

    /**
     * @return CompanyUserDto[]
     */
    public function handle(GetCompanyUsersQuery $query, JwtToken $jwtToken): array
    {
        $companyId = new CompanyId($query->companyId);
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new RuntimeException("Company with ID {$query->companyId} not found");
        }

        $localUsers = UserAR::find()
            ->where(['company_id' => $query->companyId])
            ->all();

        $result = [];
        foreach ($localUsers as $local) {
            $userData = $this->passportGateway->getUserById((string)$local->user_id, $jwtToken, QueryParams::create(expand: ['contacts']));
            if (!$userData) {
                continue;
            }

            $email = null;
            foreach ($userData['contacts'] ?? [] as $contact) {
                if (($contact['name'] ?? '') === 'email') {
                    $email = $contact['data'] ?? null;
                    break;
                }
            }

            $result[] = new CompanyUserDto(
                userId: (int)$userData['id'],
                surname: $userData['surname'] ?? '',
                name: $userData['name'] ?? '',
                patronymic: $userData['patronymic'] ?? null,
                post: $local->post,
                email: $email,
                avatar: $userData['avatar'] ?? null
            );
        }

        return $result;
    }
}