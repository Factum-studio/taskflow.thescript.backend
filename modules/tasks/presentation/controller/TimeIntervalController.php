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