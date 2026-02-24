<?php

namespace core\infrastructure\repository;

use core\application\port\IPassportGateway;
use core\application\port\IUserRepository;
use core\domain\entity\User;
use core\domain\valueObject\Identify;
use core\domain\valueObject\JwtToken;
use core\domain\valueObject\QueryParams;
use core\domain\valueObject\Contact;
use core\domain\valueObject\Post;
use yii\caching\CacheInterface;
use RuntimeException;
use Yii;

final class PassportUserRepository implements IUserRepository
{
    public function __construct(
        private readonly IPassportGateway $passportGateway,
        private readonly CacheInterface $cache,
        private readonly int $cacheTtl = 3600 //1ч
    ) {}

    public function findById(Identify $id, JwtToken $token): ?User
    {
        $cacheKey = 'user_' . $id->value();

        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }

        try {
            $params = QueryParams::create(expand: ['contacts', 'post']);
            $data = $this->passportGateway->getUserById($id->value(), $token, $params);

            if (empty($data)) {
                return null;
            }

            $user = $this->mapToUser($data);

            $this->cache->set($cacheKey, $user, $this->cacheTtl);

            return $user;
        } catch (RuntimeException $e) {
            Yii::error('Failed to fetch user from Passport: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    private function mapToUser(array $data): User
    {
        $userId = Identify::fromString((string)$data['id']);
        $contacts = array_map(
            fn(array $contactData) => new Contact(
                $contactData['name'],
                $contactData['data'],
                $contactData['confirmed'] ?? false
            ),
            $data['contacts'] ?? []
        );

        $post = null;
        if (isset($data['post']) && is_array($data['post'])) {
            $post = new Post(
                $data['post']['id'],
                $data['post']['name']
            );
        }

        return new User(
            $userId,
            $data['surname'],
            $data['name'],
            $data['patronymic'] ?? null,
            $data['dob'],
            $contacts,
            $post
        );
    }
}