<?php

namespace modules\tasks\presentation\request;

use yii\base\Model;

class ReorderBoardColumnsRequest extends Model
{
    /** @var int[] */
    public array $orderedIds;

    public function rules(): array
    {
        return [
            ['orderedIds', 'required'],
            ['orderedIds', 'each', 'rule' => ['integer']],
        ];
    }
}