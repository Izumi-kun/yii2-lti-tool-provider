Yii2 LTI Tool
======================

LTI Tool module for Yii2.

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

Add the module to the web config and configure it. The module supports the following events for handling messages from Platforms:

- `launch`
- `configure`
- `dashboard`
- `contentItem`
- `contentItemUpdate`
- `submissionReview`

Make sure to configure access to the `lti/platform` controller actions.
All messages from Platforms are handled by the `lti/tool` controller, and there are no access restrictions.

```php
$config = [
    'modules' => [
        'lti' => [
            'class' => \izumi\yii2lti\Module::class,
            'tool' => [
                'debugMode' => YII_DEBUG,
                'rsaKey' => 'A PEM formatted private key (for LTI 1.3 support)',
            ],
            'on launch' => [SiteController::class, 'ltiLaunch'],
            'on error' => [SiteController::class, 'ltiError'],
            'as access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    ['allow' => true, 'controllers' => ['lti/tool']],
                    ['allow' => true, 'controllers' => ['lti/platform'], 'roles' => ['admin']],
                ],
            ],
        ],
    ],
];
```

### Event handlers

Create event handlers according to the module configuration.

```php
namespace app\controllers;

use izumi\yii2lti\ToolEvent;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * basic-lti-launch-request handler
     * @param ToolEvent $event
     */
    public static function ltiLaunch(ToolEvent $event)
    {
        $tool = $event->sender;

        // $userPk can be used for user identity
        $userPk = $tool->user->getRecordId();
        $isAdmin = $tool->user->isStaff() || $tool->user->isAdmin();

        Yii::$app->session->set('isAdmin', $isAdmin);
        Yii::$app->session->set('userPk', $userPk);
        Yii::$app->controller->redirect(['/site/index']);

        $tool->ok = true;
        $event->handled = true;
    }

    /**
     * LTI error handler
     * @param ToolEvent $event
     * @throws BadRequestHttpException
     */
    public static function ltiError(ToolEvent $event)
    {
        $tool = $event->sender;
        $msg = $tool->message;
        if (!empty($tool->reason)) {
            Yii::error($tool->reason);
            if ($tool->debugMode) {
                $msg = $tool->reason;
            }
        }
        throw new BadRequestHttpException($msg);
    }
}
```

### Outcome

```php
use ceLTIc\LTI;

/* @var \izumi\yii2lti\Module $module */
$module = Yii::$app->getModule('lti');

$user = $module->findUserById(Yii::$app->session->get('userPk'));

$result = '0.8';
$outcome = new LTI\Outcome($result);

if ($module->doOutcomesService(LTI\Enum\ServiceAction::Write, $outcome, $user)) {
    Yii::$app->session->addFlash('success', 'Result sent successfully');
}
```

### Sample app

[https://github.com/Izumi-kun/yii2-lti-tool-provider-sample](https://github.com/Izumi-kun/yii2-lti-tool-provider-sample)

### Useful

- [LTI Platform emulator](https://saltire.lti.app/platform)
- [celtic-project/LTI-PHP/wiki](https://github.com/celtic-project/LTI-PHP/wiki)
