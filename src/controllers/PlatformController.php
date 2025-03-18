<?php
/** @noinspection PhpUnused */

/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\controllers;

use ceLTIc\LTI\Platform;
use izumi\yii2lti\models\PlatformForm;
use izumi\yii2lti\Module;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class PlatformController
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class PlatformController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verb' => [
                'class' => '\yii\filters\VerbFilter',
                'actions' => [
                    'index' => ['GET'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $platforms = Module::getInstance()->tool->getPlatforms();
        usort($platforms, function (Platform $a, Platform $b) {
            return $a->getRecordId() > $b->getRecordId() ? 1 : -1;
        });

        return $this->render('index', [
            'platforms' => $platforms,
        ]);
    }

    public function actionCreate(): Response|string
    {
        $form = new PlatformForm();
        if ($form->load(Yii::$app->getRequest()->post()) && $form->save()) {
            return $this->redirect(['update', 'id' => $form->getPlatform()->getRecordId()]);
        }

        return $this->render('platform', ['model' => $form]);
    }

    public function actionUpdate($id): Response|string
    {
        $platform = Platform::fromRecordId($id, Module::getInstance()->tool->dataConnector);
        if ($platform->getRecordId() === null) {
            throw new NotFoundHttpException();
        }

        $form = new PlatformForm();
        $form->setPlatform($platform);
        if ($form->load(Yii::$app->getRequest()->post()) && $form->save()) {
            return $this->refresh();
        }

        return $this->render('platform', [
            'model' => $form,
        ]);
    }

    public function actionDelete($id): Response
    {
        $c = Platform::fromRecordId($id, Module::getInstance()->tool->dataConnector);
        if ($c->getRecordId() === null) {
            throw new NotFoundHttpException();
        }
        $c->delete();

        return $this->redirect(['index']);
    }
}
