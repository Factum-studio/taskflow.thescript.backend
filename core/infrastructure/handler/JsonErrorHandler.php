<?php

namespace core\infrastructure\handler;

use yii\web\ErrorHandler;
use yii\web\Response;
use core\application\dto\ErrorDto;

class JsonErrorHandler extends ErrorHandler
{
    protected function renderException($exception)
    {
        if (\Yii::$app !== null && \Yii::$app->has('response')) {
            $response = \Yii::$app->response;
        } else {
            $response = new Response();
        }

        $response->format = Response::FORMAT_JSON;

        $code = $exception->statusCode ?? $exception->getCode() ?: 500;
        $message = $exception->getMessage() ?: 'Internal Server Error';

        $details = [];
        if (method_exists($exception, 'getDetails')) {
            $details = $exception->getDetails();
        }

        $errorDto = new ErrorDto($message, $code, $details);
        $response->data = $errorDto->jsonSerialize();
        $response->send();
    }
}