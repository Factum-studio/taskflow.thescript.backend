<?php

namespace modules\tasks\application\handler;

use modules\projects\application\port\IProjectAccess;
use modules\tasks\application\assembler\TaskDtoAssembler;
use modules\tasks\application\dto\TaskDto;
use modules\tasks\application\query\ListTasksQuery;
use modules\tasks\domain\repository\ITaskRepository;

class ListTasksHandler
{
    private ITaskRepository $taskRepository;
    private TaskDtoAssembler $taskDtoAssembler;
    private IProjectAccess $projectAccess;

    public function __construct(
        ITaskRepository $taskRepository,
        TaskDtoAssembler $taskDtoAssembler,
        IProjectAccess $projectAccess
    ) {
        $this->taskRepository   = $taskRepository;
        $this->taskDtoAssembler = $taskDtoAssembler;
        $this->projectAccess    = $projectAccess;
    }

    /**
     * @return TaskDto[]
     */
    public function handle(ListTasksQuery $query): array
    {
        $criteria = $query->toCriteria();

        // Получаем список досок, доступных пользователю
        $accessibleBoardIds = [];
        $projectIds = $this->projectAccess->getUserProjectIds($query->userId);
        foreach ($projectIds as $projectId) {
            $accessibleBoardIds = array_merge($accessibleBoardIds, $this->projectAccess->getProjectBoardIds($projectId));
        }
        $accessibleBoardIds = array_unique($accessibleBoardIds);

        // Если в критериях указан boardId, проверяем, что он доступен
        if (isset($criteria['board_id'])) {
            if (!in_array($criteria['board_id'], $accessibleBoardIds)) {
                return [];
            }
        } else {
            // Иначе добавляем фильтр по доступным доскам
            $criteria['board_id'] = $accessibleBoardIds;
        }

        $tasks = $this->taskRepository->findAll($criteria);
        return $this->taskDtoAssembler->toDtoList($tasks);
    }
}