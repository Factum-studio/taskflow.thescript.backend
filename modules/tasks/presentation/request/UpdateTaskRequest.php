<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class UpdateTaskRequest extends Model
{
    public ?string $title = null;
    public ?string $description = null;
    public ?int $statusId = null;
    public ?int $priorityId = null;
    public ?string $dueDate = null;
    public ?string $plannedStart = null;
    public ?string $plannedEnd = null;
    public ?int $boardId = null;
    public ?int $assignedTo = null;
    public ?int $parentId = null;

    public function rules(): array
    {
        return [
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['statusId', 'priorityId', 'boardId', 'assignedTo', 'parentId'], 'integer'],
            [['dueDate', 'plannedStart', 'plannedEnd'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['plannedEnd'], 'compare', 'compareAttribute' => 'plannedStart', 'operator' => '>=', 'type' => 'datetime', 'skipOnEmpty' => true],
        ];
    }
}