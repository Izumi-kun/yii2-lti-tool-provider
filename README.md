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

Add module to web config and configure. The module has three main events for handling messages from Tool Consumers:

- `launch` for `basic-lti-launch-request` message type
- `contentItem` for `ContentItemSelectionRequest` message type
- `register` for `ToolProxyRegistrationRequest` message type

Make sure to configure access to `lti/consumer` controller actions.

```php
$config = [
    'modules' => [
        'lti' => [
            'class' => '\izumi\yii2lti\Module',
            'on launch' => ['\app\controllers\SiteController', 'ltiLaunch'],
            'on error' => ['\app\controllers\SiteController', 'ltiError'],
            'as access' => [
                'class' => '\yii\filters\AccessControl',
                'rules' => [
                    ['allow' => true, 'controllers' => ['lti/connect']],
                    ['allow' => true, 'controllers' => ['lti/consumer'], 'roles' => ['admin']],
                ],
            ],
        ],
    ],
];
```

### Event handlers

Create event handlers to respect module config.

```php
namespace app\controllers;

use izumi\yii2lti\ToolProviderEvent;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * basic-lti-launch-request handler
     * @param ToolProviderEvent $event
     */
    public static function ltiLaunch(ToolProviderEvent $event)
    {
        $tool = $event->sender;

        // $userPk can be used for user identity
        $userPk = $tool->user->getRecordId();
        $isAdmin = $tool->user->isStaff() || $tool->user->isAdmin();

        Yii::$app->session->set('isAdmin', $isAdmin);
        Yii::$app->session->set('isLtiSession', true);
        Yii::$app->session->set('userPk', $userPk);
        Yii::$app->controller->redirect(['/site/index']);

        $tool->ok = true;
    }

    /**
     * LTI error handler
     * @param ToolProviderEvent $event
     * @throws BadRequestHttpException
     */
    public static function ltiError(ToolProviderEvent $event)
    {
        $tool = $event->sender;
        $msg = $tool->message;
        if (!empty($tool->reason)) {
            Yii::error($tool->reason);
            if ($tool->isDebugMode()) {
                $msg = $tool->reason;
            }
        }
        throw new BadRequestHttpException($msg);
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
