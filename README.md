Yii2 LTI Tool Provider
======================

LTI Tool Provider library for Yii2.

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

### Migrations

Add namespaced migrations: `izumi\yii2lti\migrations`. Apply new migrations.

### Application config

Add module to web config and configure access.

```php
$config = [
    'modules' => [
        'lti' => [
            'class' => '\izumi\yii2lti\Module',
            'as access' => [
                'class' => '\yii\filters\AccessControl',
                'rules' => [['allow' => true, 'roles' => ['admin']]],
            ],
        ],
    ],
];
```

### Connect action

Create connect action for handling requests from Tool Consumers.

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

### Outcome

```php
/* @var Module $module */
$module = Yii::$app->getModule('lti');

$user = User::fromRecordId(Yii::$app->session->get('userPk'), $module->toolProvider->dataConnector);

$result = '0.8';
$outcome = new Outcome($result);

if ($user->getResourceLink()->doOutcomesService(ResourceLink::EXT_WRITE, $outcome, $user)) {
    Yii::$app->session->addFlash('success', 'Result sent successfully');
}
```

### Sample app

[https://github.com/Izumi-kun/yii2-lti-tool-provider-sample](https://github.com/Izumi-kun/yii2-lti-tool-provider-sample)

### Useful

- [LTI Tool Consumer emulator](http://lti.tools/saltire/tc)
- [IMSGlobal/LTI-Tool-Provider-Library-PHP/wiki](https://github.com/IMSGlobal/LTI-Tool-Provider-Library-PHP/wiki)
