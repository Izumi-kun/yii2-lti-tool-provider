Yii2 LTI Tool Provider
======================

LTI Tool Provider library for Yii2

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

3. Connect action:

```php
    public function actionLtiConnect()
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

            $this->redirect($isAdmin ? 'site/admin' : 'site/index');
        });

        return $module->handleRequest();
    }
```
