<?php

declare(strict_types=1);

namespace core\presentation\controller;

use core\security\YiiIdentity;
use Yii;
use yii\base\InvalidConfigException;
use yii\rest\Controller;
use core\domain\valueObject\IdRange;
use core\application\dto\CollectionDto;
use core\application\dto\SuccessDto;
use core\application\dto\ItemDto;
use core\application\dto\ErrorDto;
use yii\base\Model;

abstract class BaseController extends Controller
{
    public int $defaultPageSize = 20;
    public array $pageSizeLimit = [1, 1000];

    /**
     * @param mixed $dto
     */
    protected function item($dto): ItemDto
    {
        return new ItemDto($dto);
    }

    /**
     * @param array<mixed> $items
     */
    protected function collection(array $items, ?int $total = null, ?int $page = null, ?int $limit = null): CollectionDto
    {
        return new CollectionDto($items, $total, $page, $limit);
    }

    /**
     * @param array<string, mixed> $details
     */
    protected function error(string $message, int $code = 400, array $details = []): ErrorDto
    {
        return new ErrorDto($message, $code, $details);
    }

    /**
     * @param mixed $data
     */
    protected function success($data = null, string $message = 'OK'): SuccessDto
    {
        return new SuccessDto($data, $message);
    }


    /**
     * Загружает multipart/form-data в типизированную Yii-модель.
     *
     * PHP не различает "поле не пришло" и пустое строковое значение на уровне
     * типизированного свойства: попытка записать "" в ?int приводит к TypeError.
     * Для API "" трактуем как отсутствие значения, а числовые поля приводим к int.
     *
     * @param array<int, string> $integerFields
     * @throws InvalidConfigException
     */
    protected function loadMultipartModel(Model $model, array $integerFields = []): void
    {
        $data = Yii::$app->request->getBodyParams();

        foreach ($data as $attribute => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$attribute] = null;
                continue;
            }

            if (in_array($attribute, $integerFields, true) && (is_int($value) || ctype_digit((string)$value))) {
                $data[$attribute] = (int)$value;
            }
        }

        $model->load($data, '');
    }

    protected function parseIdRangeFromPath(?string $param): ?IdRange
    {
        if ($param === null || trim($param) === '') {
            return null;
        }

        try {
            return IdRange::fromString($param);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Возвращает ID текущего пользователя в виде строки или null, если не аутентифицирован.
     */
    protected function getUserId(): ?string
    {
        $identity = Yii::$app->user->identity;
        return $identity instanceof YiiIdentity ? $identity->getId() : null;
    }

    protected function getUserIdentity(): ?YiiIdentity
    {
        return Yii::$app->user->identity ?? null;
    }

    protected function getLimit(): int
    {
        $limit = (int)Yii::$app->request->get('limit', $this->defaultPageSize);
        return max($this->pageSizeLimit[0], min($limit, $this->pageSizeLimit[1]));
    }

    protected function getPage(): int
    {
        return max(1, (int)Yii::$app->request->get('page', 1));
    }
}
