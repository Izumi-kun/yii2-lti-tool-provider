<?php /** @noinspection PhpUnused */

/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\controllers;

use izumi\yii2lti\Module;
use yii\web\Controller;

/**
 * Class ConnectController
 *
 * @property Module $module
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ConnectController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex(): string
    {
        ob_start();
        ob_implicit_flush(false);
        $this->module->toolProvider->handleRequest();
        return ob_get_clean();
    }
}
