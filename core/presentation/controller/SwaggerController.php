<?php

namespace core\presentation\controllers;

use yii\web\Controller;
use OpenApi\Generator;

class SwaggerController extends Controller
{
    public function actionJson()
    {
        $paths = [
            \Yii::getAlias('@core'),
            \Yii::getAlias('@modules'),
        ];
        $openapi = Generator::scan($paths);
        return $this->asJson($openapi->toJson());
    }

    public function actionUi()
    {
        return $this->renderPartial('@core/presentation/views/swagger/ui', [
            'jsonUrl' => '/docs/swagger/json',
        ]);
    }
}