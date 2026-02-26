<?php

namespace modules\tasks\application\handler;

use modules\tasks\application\assembler\TaskStatusDtoAssembler;
use modules\tasks\application\dto\TaskStatusDto;
use modules\tasks\application\query\ListStatusesQuery;
use modules\tasks\domain\repository\ITaskStatusRepository;

class ListStatusesHandler
{
    private ITaskStatusRepository $statusRepository;
    private TaskStatusDtoAssembler $dtoAssembler;

    public function __construct(
        ITaskStatusRepository $statusRepository,
        TaskStatusDtoAssembler $dtoAssembler
    ) {
        $this->statusRepository = $statusRepository;
        $this->dtoAssembler     = $dtoAssembler;
    }

    /**
     * @return TaskStatusDto[]
     */
    public function handle(ListStatusesQuery $query): array
    {
        $statuses = $this->statusRepository->findAll();
        return $this->dtoAssembler->toDtoList($statuses);
    }
}