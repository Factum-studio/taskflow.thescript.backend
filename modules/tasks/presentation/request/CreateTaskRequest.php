<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class CreateTaskRequest extends Model
{
    public string $title;
    public ?string $description = null;
    public int $columnId;
    public int $priorityId;
    public ?string $dueDate = null;
    public ?string $plannedStart = null;
    public ?string $plannedEnd = null;
    public int $boardId;
    public ?int $assignedTo = null;
    public ?int $parentId = null;

    public function rules(): array
    {
        return [
            [['title', 'columnId', 'priorityId', 'boardId'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['columnId', 'priorityId', 'boardId', 'assignedTo', 'parentId'], 'integer'],
            [['dueDate', 'plannedStart', 'plannedEnd'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['plannedEnd'], 'compare', 'compareAttribute' => 'plannedStart', 'operator' => '>=', 'type' => 'datetime', 'skipOnEmpty' => true],
        ];
    }
}