<?php

declare(strict_types=1);

namespace modules\passport\auth\presentation\controller;

use core\presentation\controller\BaseController;
use Exception;
use modules\passport\auth\application\command\HandleSsoCallbackCommand;
use modules\passport\auth\application\command\InitiateSsoLoginCommand;
use modules\passport\auth\application\handler\HandleSsoCallbackHandler;
use modules\passport\auth\application\handler\InitiateSsoLoginHandler;
use modules\passport\auth\application\port\PassportAuthPort;
use OpenApi\Attributes as OA;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\Response;

#[OA\Tag(
    name: 'auth',
    description: 'Аутентификация и работа с токенами через внешний сервис',
)]
final class AuthController extends BaseController
{
    public $layout = '@modules/passport/auth/presentation/view/layout/main';

    #[OA\Get(
        path: '/auth/login',
        description: 'Инициирует переадресацию на вход по SSO через внешний сервис',
        summary: 'SSO вход через PASSPORT',
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Отрисованна информационная страница о переходе на внешний сервис',
            ),
            new OA\Response(
                response: 302,
                description: 'Переадресация с информационной страницы на внешний SSO вход',
            ),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionLogin(): string
    {
        $state = bin2hex(random_bytes(32));
        $params = require dirname(__DIR__, 2) . '/config/params.php';

        Yii::$app->response->cookies->add(new Cookie([
            'name' => $params['state_cookie_name'],
            'value' => $state,
            'httpOnly' => true,
            'secure' => true,
            'sameSite' => Cookie::SAME_SITE_LAX,
            'path' => '/auth',
            'expire' => time() + 600,
        ]));

        $targetUrl = Yii::$container
            ->get(InitiateSsoLoginHandler::class)
            ->handle(new InitiateSsoLoginCommand($state));

        return $this->renderInfo(
            'Переадресация PASSPORT',
            'Переадресуем тебя на вход',
            '@modules/passport/auth/presentation/view/login/redirect',
            $targetUrl,
        );
    }

    #[OA\Get(
        path: '/auth/callback',
        description: 'Меняет полученный код на пару токенов',
        summary: 'callback sso аутентификации',
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Отрисованна информационная страница о получении токенов от внешнего сервиса',
            ),
            new OA\Response(
                response: 302,
                description: 'Переадресация с информационной страницы на целевой сервис',
            ),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionCallback(): string
    {
        $code = Yii::$app->request->get('code');
        $state = Yii::$app->request->get('state');
        $params = require dirname(__DIR__, 2) . '/config/params.php';

        if (!is_string($code) || $code === '' || !is_string($state) || $state === '') {
            throw new BadRequestHttpException('OAuth callback requires code and state');
        }

        $expectedState = Yii::$app->request->cookies->getValue($params['state_cookie_name']);
        Yii::$app->response->cookies->remove(new Cookie([
            'name' => $params['state_cookie_name'],
            'path' => '/auth',
        ]));

        if (!is_string($expectedState) || $expectedState === '') {
            throw new BadRequestHttpException('OAuth state is missing or expired');
        }

        $tokens = Yii::$container
            ->get(HandleSsoCallbackHandler::class)
            ->handle(new HandleSsoCallbackCommand($code, $state, $expectedState));

        $this->setTokenCookie(
            $params['access_token_cookie_name'],
            $tokens->accessToken,
            $tokens->expiresIn,
        );

        if ($tokens->refreshToken !== null) {
            $this->setTokenCookie(
                $params['refresh_token_cookie_name'],
                $tokens->refreshToken,
                $params['oauth2_refresh_token_ttl'],
            );
        }

        return $this->renderInfo(
            'Договариваемся с PASSPORT',
            'PASSPORT подтвердил вход. Ещё немного — и ты внутри.',
            '@modules/passport/auth/presentation/view/login/exchange',
            $params['frontend_url'],
        );
    }

    #[OA\Get(
        path: '/auth/logout',
        description: 'Отправляет запрос на выход из аккаунта',
        summary: 'proxy выхода из аккаунта',
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Сессия завершена',
            ),
            new OA\Response(
                response: 401,
                description: 'Пользователь не аутентифицирован',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function actionLogout(): Response
    {
        $request = Yii::$app->request;
        $result = Yii::$container->get(PassportAuthPort::class)->logout(
            $request->getHeaders()->get('Cookie'),
            $request->getHeaders()->get('Authorization'),
        );

        foreach ($result['headers'] as $name => $values) {
            foreach ($values as $value) {
                Yii::$app->response->headers->add($name, $value);
            }
        }

        $params = require dirname(__DIR__, 2) . '/config/params.php';
        Yii::$app->response->cookies->remove(new Cookie([
            'name' => $params['access_token_cookie_name'],
            'path' => '/',
        ]));
        Yii::$app->response->cookies->remove(new Cookie([
            'name' => $params['refresh_token_cookie_name'],
            'path' => '/',
        ]));

        Yii::$app->response->statusCode = $result['status'];
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->data = $result['body'];

        return Yii::$app->response;
    }

    #[OA\Get(
        path: '/auth/introspect',
        description: 'Отправляет запрос на интроспекцию токена',
        summary: 'proxy интроспекции токена',
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Результат интроспекции токена',
            ),
            new OA\Response(
                response: 401,
                description: 'Пользователь не аутентифицирован',
                content: new OA\JsonContent(ref: '#/components/schemas/Error'),
            ),
        ],
    )]
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionIntrospect(): Response
    {
        $params = require dirname(__DIR__, 2) . '/config/params.php';
        $accessToken = Yii::$app->request->cookies->getValue($params['access_token_cookie_name']);
        if (!is_string($accessToken) || $accessToken === '') {
            throw new BadRequestHttpException('Access token is missing');
        }

        $result = Yii::$container->get(PassportAuthPort::class)->introspect($accessToken);

        Yii::$app->response->statusCode = $result['status'];
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->data = $result['body'];

        return Yii::$app->response;
    }

    private function setTokenCookie(string $name, string $value, int $ttl): void
    {
        Yii::$app->response->cookies->add(new Cookie([
            'name' => $name,
            'value' => $value,
            'httpOnly' => true,
            'secure' => true,
            'sameSite' => Cookie::SAME_SITE_LAX,
            'path' => '/',
            'expire' => time() + max(1, $ttl),
        ]));
    }

    private function renderInfo(string $title, string $description, string $view, string $targetUrl): string
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        $this->view->title = $title;
        $this->view->params['description'] = $description;

        return $this->render($view, [
            'targetUrl' => $targetUrl,
            'delay' => 3.1,
        ]);
    }
}
