<?php /** @noinspection PhpUnused */

/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\controllers;

use ceLTIc\LTI\Jwt\Jwt;
use izumi\yii2lti\Module;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class ToolController
 *
 * @property Module $module
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ToolController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * Processes a launch request from an LTI platform
     * @return string
     */
    public function actionConnect(): string
    {
        ob_start();
        ob_implicit_flush(false);
        $this->module->tool->handleRequest();
        return ob_get_clean();
    }

    /**
     * Returns the JWKS
     * @return Response
     */
    public function actionJwks(): Response
    {
        $jwk = Jwt::getJwtClient();
        $tool = Module::getInstance()->tool;
        return $this->asJson($tool->rsaKey ? $jwk::getJWKS($tool->rsaKey, $tool->signatureMethod, $tool->kid) : []);
    }
}
