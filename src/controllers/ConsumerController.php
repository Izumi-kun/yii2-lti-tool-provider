<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\controllers;

use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use izumi\yii2lti\models\ConsumerForm;
use izumi\yii2lti\Module;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class ConsumerController
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ConsumerController extends Controller
{
    public function behaviors()
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

    public function actionIndex()
    {
        $consumers = Module::getInstance()->toolProvider->getConsumers();
        usort($consumers, function (ToolConsumer $a, ToolConsumer $b){
            return $a->getRecordId() > $b->getRecordId() ? 1 : -1;
        });

        return $this->render('index', [
            'consumers' => $consumers,
        ]);
    }

    public function actionCreate()
    {
        $form = new ConsumerForm();
        if ($form->load(Yii::$app->getRequest()->post()) && $form->save()) {
            return $this->redirect(['update', 'id' => $form->getConsumer()->getRecordId()]);
        }

        return $this->render('consumer', ['model' => $form]);
    }

    public function actionUpdate($id)
    {
        $c = ToolConsumer::fromRecordId($id, Module::getInstance()->toolProvider->dataConnector);
        if ($c->getRecordId() === null) {
            throw new NotFoundHttpException();
        }

        $form = new ConsumerForm();
        $form->setConsumer($c);
        if ($form->load(Yii::$app->getRequest()->post()) && $form->save()) {
            return $this->refresh();
        }

        return $this->render('consumer', [
            'model' => $form,
        ]);
    }

    public function actionDelete($id)
    {
        $c = ToolConsumer::fromRecordId($id, Module::getInstance()->toolProvider->dataConnector);
        if ($c->getRecordId() === null) {
            throw new NotFoundHttpException();
        }
        $c->delete();

        return $this->redirect(['index']);
    }
}
