<?php
namespace core\application\handler;

use core\application\dto\AuthorDto;
use core\application\port\IAuthorRepository;
use core\application\port\IPassportGateway;
use core\application\query\GetAllAuthorsQuery;
use core\domain\valueObject\JwtToken;

class GetAllAuthorsHandler
{
    public function __construct(
        private IAuthorRepository $authorRepository,
        private IPassportGateway $passportGateway
    ) {}

    /**
     * @return AuthorDto[]
     */
    public function handle(GetAllAuthorsQuery $query, JwtToken $jwtToken): array
    {
        $authors = $this->authorRepository->findAll();
        if (empty($authors)) {
            return [];
        }

        $result = [];
        foreach ($authors as $author) {
            $userData = $this->passportGateway->getUserById(
                (string)$author->getUserId(),
                $jwtToken
            );
            if (!$userData) {
                continue;
            }
            $avatar = $userData['avatar'] ?? null;
            $uiName = $userData['surname'] . ' ' . $userData['name'];
            if (isset($userData['patronymic']) && $userData['patronymic'] != null && $userData['patronymic'] != '') {
                $uiName = $userData['name'] . ' ' . $userData['patronymic'];
            }

            $result[] = AuthorDto::fromEntity($author, $avatar, $uiName);
        }

        return $result;
    }
}