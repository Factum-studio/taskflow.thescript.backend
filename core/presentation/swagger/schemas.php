<?php
/**
 * @OA\Schema(
 *     schema="Collection",
 *     required={"items"},
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(type="object"),
 *         description="Массив элементов коллекции"
 *     ),
 *     @OA\Property(
 *         property="_meta",
 *         type="object",
 *         description="Мета-информация о пагинации",
 *         @OA\Property(
 *             property="total",
 *             type="integer",
 *             description="Общее количество элементов",
 *             example=100
 *         ),
 *         @OA\Property(
 *             property="page",
 *             type="integer",
 *             description="Текущая страница",
 *             example=1
 *         ),
 *         @OA\Property(
 *             property="limit",
 *             type="integer",
 *             description="Количество элементов на странице",
 *             example=20
 *         ),
 *         @OA\Property(
 *             property="pages",
 *             type="integer",
 *             description="Общее количество страниц",
 *             example=5,
 *             nullable=true
 *         )
 *     )
 * )
 * @OA\Schema(
 *     schema="Item",
 *     @OA\Property(property="item", type="object")
 * )
 * @OA\Schema(
 *     schema="Error",
 *     @OA\Property(property="error", type="object",
 *         @OA\Property(property="code", type="integer"),
 *         @OA\Property(property="message", type="string"),
 *         @OA\Property(property="details", type="object", additionalProperties=true)
 *     )
 * )
 */
class MainSchemasDefinitions {}

/**
 * @OA\Schema(
 *     schema="Contact",
 *     required={"type", "value"},
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"tg", "phone", "email", "vk", "discord"},
 *         description="Тип контакта"
 *     ),
 *     @OA\Property(
 *         property="value",
 *         type="string",
 *         description="Значение контакта"
 *     ),
 *     @OA\Property(
 *         property="confirmed",
 *         type="boolean",
 *         description="Флаг подтверждения"
 *     )
 * )
 */
class ContactSchema {}

/**
 * @OA\Schema(
 *     schema="Post",
 *     required={"id", "name"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID должности"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Название должности"
 *     )
 * )
 */
class PostSchema {}

/**
 * @OA\Schema(
 *     schema="User",
 *     required={"id", "surname", "name", "dob"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID пользователя"
 *     ),
 *     @OA\Property(
 *         property="surname",
 *         type="string",
 *         description="Фамилия"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Имя"
 *     ),
 *     @OA\Property(
 *         property="patronymic",
 *         type="string",
 *         nullable=true,
 *         description="Отчество"
 *     ),
 *     @OA\Property(
 *         property="full_name",
 *         type="string",
 *         description="Полное имя (Фамилия Имя Отчество)"
 *     ),
 *     @OA\Property(
 *         property="dob",
 *         type="integer",
 *         format="timestamp",
 *         description="Дата рождения (timestamp)"
 *     ),
 *     @OA\Property(
 *         property="contacts",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Contact"),
 *         description="Контакты пользователя"
 *     ),
 *     @OA\Property(
 *         property="post",
 *         ref="#/components/schemas/Post",
 *         nullable=true,
 *         description="Должность пользователя"
 *     )
 * )
 */
class UserSchema {}