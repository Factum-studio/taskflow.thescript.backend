<?php

namespace core\presentation\controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RedisException;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\web\Controller;
use yii\web\Response;
use Redis;
use yii\web\UnauthorizedHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'stream',
    description: 'SSE подключение'
)]
class StreamController extends Controller
{
    #[OA\Get(
        path: '/events',
        description: 'Подключение к Server-Sent Events (SSE) потоку для получения real-time уведомлений пользователя. '
        . 'Требуется токен, полученный через `/events/token`. Поток отправляет события в формате `event: message` и `data: {...}`.',
        summary: 'Подписка на поток событий (SSE)',
        security: [['bearerAuth' => []]],
        tags: ['stream'],
        parameters: [
            new OA\Parameter(
                name: 'token',
                description: 'JWT токен для SSE (выдаётся эндпоинтом `/events/token`)',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешное подключение к потоку событий. Content-Type: text/event-stream',
                content: new OA\MediaType(
                    mediaType: 'text/event-stream',
                    schema: new OA\Schema(
                        type: 'string',
                        example: "event: message\ndata: {\"event\":\"notification\",\"data\":{\"subject\":\"Новое уведомление\",\"body\":\"...\"}}\n\n"
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован – отсутствует или неверный токен',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws RedisException
     */
    public function actionEvents(): void
    {
        $tokenParam = Yii::$app->request->get('token');
        if (!$tokenParam) {
            $this->sendError('Missing token');
            return;
        }

        try {
            $jwt = JWT::decode($tokenParam, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $decoded = (array)$jwt;

            if (($decoded['scope'] ?? null) !== 'sse') {
                throw new \Exception('Invalid scope');
            }

            $userId = $decoded['sub'] ?? null;
            if (!$userId) {
                throw new \Exception('Missing user id');
            }
        } catch (\Throwable $e) {
            $this->sendError('Invalid token');
            return;
        }

        // Отключаем буферизацию и таймаут
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        Yii::$app->response->format = Response::FORMAT_RAW;

        /** @var Redis $redis */
        $redis = Yii::$container->get(Redis::class);
        $channel = "user:{$userId}";

        $redis->subscribe([$channel], function ($redis, $channel, $message) {
            echo "event: message\n";
            echo "data: {$message}\n\n";
            ob_flush();
            flush();
        });
    }

    private function sendError(string $message): void
    {
        header('Content-Type: text/event-stream');
        echo "event: error\ndata: {$message}\n\n";
        Yii::$app->response->send();
    }

    #[OA\Get(
        path: '/events/token',
        description: 'Генерирует кратковременный JWT токен для подключения к SSE потоку.',
        summary: 'Получить токен для SSE',
        security: [['bearerAuth' => []]],
        tags: ['stream'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Токен успешно сгенерирован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован – требуется авторизация через JWT (Bearer)',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    /**
     * @throws UnauthorizedHttpException
     */
    public function actionSseToken(): array
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            throw new UnauthorizedHttpException();
        }
        $payload = [
            'sub' => $user->getId(),
            'exp' => time() + 900, // 15 минут
            'scope' => 'sse',
        ];
        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
        return ['token' => $token];
    }
}