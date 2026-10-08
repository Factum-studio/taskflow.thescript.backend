<?php

declare(strict_types=1);
/**
 * @OA\Info(
 *     title="TASKFLOW.thescript",
 *     version="2.0.0",
 *     description="CRM API для управления задачами системы МОПС",
 *     @OA\Contact(
 *         email="company@thescript.agency"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="API dev server"
 * )
 * @OA\Server(
 *     url="https://api.resume.thescript.agency",
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
 *     description="Bearer access token в заголовке Authorization"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="accessTokenCookie",
 *     type="apiKey",
 *     in="cookie",
 *     name="access_token",
 *     description="HttpOnly access token cookie"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="refreshTokenCookie",
 *     type="apiKey",
 *     in="cookie",
 *     name="refresh_token",
 *     description="HttpOnly refresh token cookie"
 * )
 */
class InfoDefinitions
{
}
