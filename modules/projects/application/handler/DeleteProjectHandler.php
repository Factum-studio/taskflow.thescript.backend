<?php

namespace modules\projects\application\handler;

use modules\projects\application\command\DeleteProjectCommand;
use modules\projects\domain\repository\IProjectRepository;
use modules\projects\domain\valueObject\ProjectId;
use modules\projects\domain\valueObject\UserId;
use RuntimeException;

class DeleteProjectHandler
{
    private IProjectRepository $projectRepository;

    public function __construct(IProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function handle(DeleteProjectCommand $command): void
    {
        $projectId = new ProjectId($command->id);
        $project = $this->projectRepository->findById($projectId);

        if ($project === null) {
            throw new RuntimeException("Project with ID {$command->id} not found");
        }

        // Проверка прав: только владелец
        if (!$project->isOwner(new UserId($command->deletedBy))) {
            throw new RuntimeException("Only project owner can delete the project");
        }

        // Запрет удаления personal-проекта
        if ($project->getType()->isPersonal()) {
            throw new RuntimeException("Personal project cannot be deleted");
        }

        // TODO: каскадное удаление досок? Пока просто удаляем проект (каскад в БД настроен)
        // Но нужно также удалить связи с участниками
        // Для простоты оставим пока так
        $this->projectRepository->remove($project);
    }
}