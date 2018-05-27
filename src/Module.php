<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use Yii;

/**
 * Module.
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class Module extends \yii\base\Module
{
    const EVENT_LAUNCH = 'launch';
    const EVENT_REGISTER = 'register';
    const EVENT_CONTENT_ITEM = 'contentItem';
    const EVENT_ERROR = 'error';

    /**
     * @var string|array|ToolProvider
     */
    public $toolProvider = ToolProvider::class;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->toolProvider = Yii::createObject($this->toolProvider);
    }

    /**
     * Process an incoming request
     * @return string Output to be displayed
     */
    public function handleRequest(): string
    {
        ob_start();
        ob_implicit_flush(false);
        $this->toolProvider->handleRequest();
        $result = ob_get_clean();
        return $result;
    }
}
