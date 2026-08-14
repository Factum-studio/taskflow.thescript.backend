<?php
namespace core\presentation\controller;

use core\application\command\SubmitIdeaCommand;
use core\application\command\SubmitRatingCommand;
use core\application\dto\CollectionDto;
use core\application\dto\ErrorDto;
use core\application\dto\FeedbackIdeaDto;
use core\application\dto\FeedbackStatsDto;
use core\application\dto\ItemDto;
use core\application\dto\SuccessDto;
use core\application\handler\GetFeedbackStatsHandler;
use core\application\handler\SubmitIdeaHandler;
use core\application\handler\SubmitRatingHandler;
use core\application\port\IFeedbackIdeaRepository;
use core\application\query\GetFeedbackStatsQuery;
use OpenApi\Attributes as OA;
use Throwable;
use Yii;
use yii\web\BadRequestHttpException;

#[OA\Tag(
    name: 'feedback',
    description: 'Сбор отзывов и предложений'
)]
class FeedbackController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly SubmitRatingHandler $submitRatingHandler,
        private readonly SubmitIdeaHandler $submitIdeaHandler,
        private readonly GetFeedbackStatsHandler $statsHandler,
        private readonly IFeedbackIdeaRepository $ideaRepository,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    #[OA\Post(
        path: '/feedback/rating',
        summary: 'Отправить оценку сервиса',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['speed', 'functionality', 'design', 'usability'],
                properties: [
                    new OA\Property(property: 'speed', type: 'integer', maximum: 5, minimum: 0),
                    new OA\Property(property: 'functionality', type: 'integer', maximum: 5, minimum: 0),
                    new OA\Property(property: 'design', type: 'integer', maximum: 5, minimum: 0),
                    new OA\Property(property: 'usability', type: 'integer', maximum: 5, minimum: 0),
                ]
            )
        ),
        tags: ['feedback'],
        responses: [
            new OA\Response(response: 200, description: 'Оценка сохранена'),
            new OA\Response(response: 400, description: 'Некорректные данные'),
            new OA\Response(response: 401, description: 'Требуется авторизация')
        ]
    )]
    public function actionSubmitRating(): SuccessDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = Yii::$app->request;
        $speed = $request->post('speed');
        $functionality = $request->post('functionality');
        $design = $request->post('design');
        $usability = $request->post('usability');

        if (!is_numeric($speed) || !is_numeric($functionality) || !is_numeric($design) || !is_numeric($usability)) {
            return $this->error('All ratings must be integers between 0 and 5');
        }

        try {
            $command = new SubmitRatingCommand(
                $userId,
                (int)$speed,
                (int)$functionality,
                (int)$design,
                (int)$usability
            );
            $this->submitRatingHandler->handle($command);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'feedback');
            return $this->error('Failed to save rating', 500);
        }

        return $this->success(null, 'Rating submitted');
    }

    #[OA\Post(
        path: '/feedback/idea',
        summary: 'Отправить идею или предложение',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['type', 'comment'],
                properties: [
                    new OA\Property(property: 'type', type: 'string', enum: ['idea', 'bug', 'feature', 'improvement', 'question']),
                    new OA\Property(property: 'comment', type: 'string')
                ]
            )
        ),
        tags: ['feedback'],
        responses: [
            new OA\Response(response: 200, description: 'Идея сохранена'),
            new OA\Response(response: 400, description: 'Некорректные данные')
        ]
    )]
    public function actionSubmitIdea(): ItemDto|ErrorDto|array
    {
        $userId = $this->getUserId();
        if (!$userId) {
            return $this->error('User not authenticated', 401);
        }

        $request = Yii::$app->request;
        $type = $request->post('type');
        $comment = $request->post('comment');

        if (empty($type) || empty($comment)) {
            return $this->error('Type and comment are required', 400);
        }

        try {
            $command = new SubmitIdeaCommand($userId, $type, $comment);
            $idea = $this->submitIdeaHandler->handle($command);
            return $this->item(FeedbackIdeaDto::fromEntity($idea));
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (Throwable $e) {
            Yii::error($e->getMessage(), 'feedback');
            return $this->error('Failed to save idea', 500);
        }
    }

    #[OA\Get(
        path: '/feedback/idea',
        summary: 'Список всех идей и предложений',
        security: [['bearerAuth' => []]],
        tags: ['feedback'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string', enum: ['idea', 'bug', 'feature', 'improvement', 'question'])),
            new OA\Parameter(name: 'is_implemented', in: 'query', schema: new OA\Schema(type: 'boolean'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список идей',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/FeedbackIdea')),
                        new OA\Property(property: '_meta', ref: '#/components/schemas/Collection/properties/_meta')
                    ]
                )
            )
        ]
    )]
    public function actionIdeas(): CollectionDto|array
    {
        $filters = [];
        $type = Yii::$app->request->get('type');
        if ($type) {
            $filters['type'] = $type;
        }
        $isImplemented = Yii::$app->request->get('is_implemented');
        if ($isImplemented !== null) {
            $filters['is_implemented'] = filter_var($isImplemented, FILTER_VALIDATE_BOOLEAN);
        }

        $ideas = $this->ideaRepository->findAll($filters);
        $dtos = array_map([FeedbackIdeaDto::class, 'fromEntity'], $ideas);
        return $this->collection($dtos);
    }

    #[OA\Get(
        path: '/feedback/stats',
        summary: 'Статистика по отзывам и предложениям',
        security: [['bearerAuth' => []]],
        tags: ['feedback'],
        responses: [
            new OA\Response(response: 200, description: 'Статистика', content: new OA\JsonContent(ref: '#/components/schemas/FeedbackStats'))
        ]
    )]
    public function actionStats(): ItemDto|array
    {
        $stats = $this->statsHandler->handle(new GetFeedbackStatsQuery());
        return $this->item($stats);
    }
}