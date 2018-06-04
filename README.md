Yii2 LTI Tool Provider
======================

LTI Tool Provider library for Yii2

[![Latest Stable Version](https://poser.pugx.org/izumi-kun/yii2-lti-tool-provider/v/stable)](https://packagist.org/packages/izumi-kun/yii2-lti-tool-provider)
[![Total Downloads](https://poser.pugx.org/izumi-kun/yii2-lti-tool-provider/downloads)](https://packagist.org/packages/izumi-kun/yii2-lti-tool-provider)
[![License](https://poser.pugx.org/izumi-kun/yii2-lti-tool-provider/license)](https://packagist.org/packages/izumi-kun/yii2-lti-tool-provider)

Installation
------------

```
composer require izumi-kun/yii2-lti-tool-provider
```

Usage
-----

1. Add namespaced migrations: `izumi\yii2lti\migrations`. Apply new migrations.

2. Add module to web-config:
    ```php
    $config = [
        'modules' => [
            'lti' => [
                'class' => \izumi\yii2lti\Module::class,
            ],
        ],
    ];
    ```

3. Create connect action:
    ```php
    use izumi\yii2lti\Module;
    use izumi\yii2lti\ToolProviderEvent;
    use Yii;
    use yii\web\Controller;
    
    class ConnectController extends Controller
    {
        public $enableCsrfValidation = false;
    
        public function actionIndex()
        {
            /* @var Module $module */
            $module = Yii::$app->getModule('lti');
    
            // launch action
            $module->on(Module::EVENT_LAUNCH, function (ToolProviderEvent $event){
                $tool = $event->sender;
    
                // $userPk can be used for user identity
                $userPk = $tool->user->getRecordId();
                $isAdmin = $tool->user->isStaff() || $tool->user->isAdmin();
    
                Yii::$app->session->set('isAdmin', $isAdmin);
                Yii::$app->session->set('userPk', $userPk);
    
                $this->redirect(['site/index']);
                $tool->ok = true;
            });
    
            $module->on(Module::EVENT_ERROR, function (ToolProviderEvent $event){
                Yii::error($event->sender->reason);
            });
    
            return $module->handleRequest();
        }
    }
    ```
