<?php
/**
 * @OA\Schema(
 *     schema="Collection",
 *     @OA\Property(property="items", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="total", type="integer")
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