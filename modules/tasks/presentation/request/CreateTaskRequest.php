<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class CreateTaskRequest extends Model
{
    public string $title;
    public ?string $description = null;
    public int $statusId;
    public int $priorityId;
    public ?string $dueDate = null; // формат Y-m-d H:i:s
    public int $boardId;
    public ?int $assignedTo = null;
    public ?int $parentId = null;

    public function rules(): array
    {
        return [
            [['title', 'statusId', 'priorityId', 'boardId'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['statusId', 'priorityId', 'boardId', 'assignedTo', 'parentId'], 'integer'],
            [['dueDate'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }
}