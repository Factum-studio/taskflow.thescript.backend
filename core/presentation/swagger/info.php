<?php
/**
 * @OA\Info(
 *     title="TASKFLOW CRM API",
 *     version="1.0.0",
 *     description="CRM API for TASKFLOW project",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="API dev server"
 * )
 * @OA\Server(
 *     url="https://api.taskflow.thescript.agency",
 *     description="API prod server"
 * )
 * @OA\OpenApi(
 *     openapi="3.0.0"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Используйте JWT токен для аутентификации"
 * ),
 */
class InfoDefinitions {}