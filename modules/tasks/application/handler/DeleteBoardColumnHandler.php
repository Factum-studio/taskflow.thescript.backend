<?php

declare(strict_types=1);

namespace modules\tasks\application\handler;

use modules\tasks\application\command\DeleteBoardColumnCommand;
use modules\projectRbac\application\port\ITaskAccessService;
use modules\tasks\domain\repository\IBoardColumnRepository;
use modules\tasks\domain\valueObject\ColumnId;
use RuntimeException;

class DeleteBoardColumnHandler
{
    private IBoardColumnRepository $columnRepository;
    private ITaskAccessService $taskAccess;

    public function __construct(
        IBoardColumnRepository $columnRepository,
        ITaskAccessService $taskAccess,
    ) {
        $this->columnRepository = $columnRepository;
        $this->taskAccess       = $taskAccess;
    }

    public function handle(DeleteBoardColumnCommand $command): void
    {
        $columnId = new ColumnId($command->id);
        $column = $this->columnRepository->findById($columnId);
        if (!$column) {
            throw new RuntimeException("Column with ID {$command->id} not found");
        }
        if (!$this->taskAccess->canDeleteColumn($command->deletedBy, $command->id)) {
            throw new RuntimeException('You are not allowed to delete this column');
        }
        $this->columnRepository->remove($column);
    }
}
