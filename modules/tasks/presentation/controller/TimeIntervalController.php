<?php

namespace modules\tasks\presentation\controller;

use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\ItemDto;
use core\presentation\controller\BaseController;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use modules\tasks\application\command\LogManualIntervalCommand;
use modules\tasks\application\command\StartTimerCommand;
use modules\tasks\application\command\StopTimerCommand;
use modules\tasks\application\handler\GetDailySummaryHandler;
use modules\tasks\application\handler\GetTaskTimeSummaryHandler;
use modules\tasks\application\handler\ListTimeIntervalsHandler;
use modules\tasks\application\handler\LogManualIntervalHandler;
use modules\tasks\application\handler\StartTimerHandler;
use modules\tasks\application\handler\StopTimerHandler;
use modules\tasks\application\query\GetDailySummaryQuery;
use modules\tasks\application\query\GetTaskTimeSummaryQuery;
use modules\tasks\application\query\ListTimeIntervalsQuery;
use modules\tasks\presentation\request\DailySummaryRequest;
use modules\tasks\presentation\request\LogIntervalRequest;
use modules\tasks\presentation\request\StartTimerRequest;
use modules\tasks\presentation\request\StopTimerRequest;
use modules\tasks\presentation\request\TaskTimeSummaryRequest;
use modules\tasks\presentation\request\TimeIntervalsListRequest;
use RuntimeException;
use Throwable;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'time-intervals',
    description: 'Учёт времени и диаграммы занятости'
)]
class TimeIntervalController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly StartTimerHandler $startTimerHandler,
        private readonly StopTimerHandler $stopTimerHandler,
        private readonly LogManualIntervalHandler $logIntervalHandler,
        private readonly ListTimeIntervalsHandler $listTimeIntervalsHandler,
        private readonly GetDailySummaryHandler $getDailySummaryHandler,
        private readonly GetTaskTimeSummaryHandler $getTaskTimeSummaryHandler,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Get(
        path: '/time-interval',
        summary: 'Список временных интервалов',
        security: [['bearerAuth' => []]],
        tags: ['time-intervals'],
        parameters: [
            new OA\Parameter(name: 'taskId', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'userId', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'activeOnly', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/TimeInterval')
                        ),
                        new OA\Property(
                            property: '_meta',
                            ref: '#/components/schemas/Collection/properties/_meta'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация')
        ]
    )]
    /**
     * @throws Exception
     */
    public function actionIndex(): ErrorDto|CollectionDto|array
    {
        $request = new TimeIntervalsListRequest();
        $request->load(Yii::$app->request->get(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $from = $request->from ? new DateTimeImmutable($request->from, new DateTimeZone('UTC')) : null;
        $to = $request->to ? new DateTimeImmutable($request->to, new DateTimeZone('UTC')) : null;

        $query = new ListTimeIntervalsQuery(
            taskId: $request->taskId,
            userId: $request->userId,
            from: $from,
            to: $to,
            activeOnly: $request->activeOnly ?? false
        );

        $intervals = $this->listTimeIntervalsHandler->handle($query);
        return $this->collection($intervals);
    }

    #[OA\Post(
        path: '/time-interval/start',
        summary: 'Запустить таймер (начать интервал)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StartTimerRequest')
        ),
        tags: ['time-intervals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Таймер запущен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/TimeInterval'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws ServerErrorHttpException
     */
    public function actionStart(): ErrorDto|ItemDto|array
    {
        $request = new StartTimerRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new StartTimerCommand(
            taskId: $request->taskId,
            userId: $userId,
            comment: $request->comment
        );

        try {
            $intervalDto = $this->startTimerHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to start timer', 0, $e);
        }

        return $this->item($intervalDto);
    }

    #[OA\Post(
        path: '/time-interval/stop',
        summary: 'Остановить таймер (завершить интервал)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StopTimerRequest')
        ),
        tags: ['time-intervals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Таймер остановлен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/TimeInterval'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Активный интервал не найден'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionStop(): ErrorDto|ItemDto|array
    {
        $request = new StopTimerRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $command = new StopTimerCommand(
            intervalId: $request->intervalId,
            userId: $userId,
            comment: $request->comment
        );

        try {
            $intervalDto = $this->stopTimerHandler->handle($command);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to stop timer', 0, $e);
        }

        return $this->item($intervalDto);
    }

    #[OA\Post(
        path: '/time-interval',
        summary: 'Ручной ввод интервала',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LogIntervalRequest')
        ),
        tags: ['time-intervals'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Интервал сохранён',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/TimeInterval'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionCreate(): ErrorDto|ItemDto|array
    {
        $request = new LogIntervalRequest();
        $request->load(Yii::$app->request->post(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $start = new DateTimeImmutable($request->startTime, new DateTimeZone('UTC'));
        $end = new DateTimeImmutable($request->endTime, new DateTimeZone('UTC'));

        $command = new LogManualIntervalCommand(
            taskId: $request->taskId,
            userId: $userId,
            startTime: $start,
            endTime: $end,
            comment: $request->comment
        );

        try {
            $intervalDto = $this->logIntervalHandler->handle($command);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to log interval', 0, $e);
        }

        return $this->item($intervalDto);
    }

    #[OA\Get(
        path: '/time-interval/daily-summary',
        summary: 'Ежедневная сводка по времени',
        security: [['bearerAuth' => []]],
        tags: ['time-intervals'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/DailySummary')
                        ),
                        new OA\Property(
                            property: '_meta',
                            ref: '#/components/schemas/Collection/properties/_meta'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 422, description: 'Ошибка валидации')
        ]
    )]
    /**
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionDailySummary(): ErrorDto|CollectionDto|array
    {
        $request = new DailySummaryRequest();
        $request->load(Yii::$app->request->get(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $date = new DateTimeImmutable($request->date, new DateTimeZone('UTC'));
        $query = new GetDailySummaryQuery($request->userId, $date);

        try {
            $summaries = $this->getDailySummaryHandler->handle($query);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to get daily summary', 0, $e);
        }

        return $this->collection($summaries);
    }

    #[OA\Get(
        path: '/task/{taskId}/time-summary',
        summary: 'Сводка по времени задачи с разбивкой на блоки (диаграмма занятости)',
        security: [['bearerAuth' => []]],
        tags: ['time-intervals'],
        parameters: [
            new OA\Parameter(name: 'taskId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'userId', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'granularity', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['minute', 'ten_minutes', 'hour'])),
            new OA\Parameter(name: 'mode', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['merged', 'separate', 'overlap'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'item',
                            ref: '#/components/schemas/TaskTimeSummary'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Требуется авторизация'),
            new OA\Response(response: 404, description: 'Задача не найдена')
        ]
    )]
    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function actionTaskSummary(int $taskId): ErrorDto|ItemDto|array
    {
        $request = new TaskTimeSummaryRequest();
        $request->load(Yii::$app->request->get(), '');
        if (!$request->validate()) {
            return $this->error('Validation failed', 422, $request->getErrors());
        }

        $from = $request->from ? new DateTimeImmutable($request->from, new DateTimeZone('UTC')) : null;
        $to = $request->to ? new DateTimeImmutable($request->to, new DateTimeZone('UTC')) : null;

        $query = new GetTaskTimeSummaryQuery(
            taskId: $taskId,
            userId: $request->userId,
            from: $from,
            to: $to,
            granularity: $request->granularity,
            mode: $request->mode
        );

        try {
            $summary = $this->getTaskTimeSummaryHandler->handle($query);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'tasks');
            throw new ServerErrorHttpException('Failed to get task time summary', 0, $e);
        }

        return $this->item($summary);
    }
}