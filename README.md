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

Add module to web config and configure. The module support those events for handling messages from Platforms:

- `launch`
- `configure`
- `dashboard`
- `contentItem`
- `contentItemUpdate`
- `submissionReview`

Make sure to configure access to `lti/platform` controller actions.
All messages from Platforms handles by `lti/tool` controller and there is no access restrictions.

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
                    ['allow' => true, 'controllers' => ['lti/tool']],
                    ['allow' => true, 'controllers' => ['lti/platform'], 'roles' => ['admin']],
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
