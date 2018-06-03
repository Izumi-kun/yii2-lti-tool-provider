<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use Yii;
use yii\base\Configurable;

/**
 * ToolProvider
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ToolProvider extends \IMSGlobal\LTI\ToolProvider\ToolProvider implements Configurable
{

    public function __construct($config = [])
    {
        Yii::configure($this, $config);

        parent::__construct($this->dataConnector);
    }

    /**
     * @param string $eventName
     * @return bool
     */
    protected function processRequest($eventName)
    {
        if (Module::getInstance()->hasEventHandlers($eventName)) {
            Module::getInstance()->trigger($eventName, new ToolProviderEvent($this));
            return $this->ok;
        }

        return $this->onError();
    }

    /**
     * @inheritdoc
     */
    public function onLaunch()
    {
        return $this->processRequest(Module::EVENT_LAUNCH);
    }

    /**
     * @inheritdoc
     */
    public function onRegister()
    {
        return $this->processRequest(Module::EVENT_REGISTER);
    }

    /**
     * @inheritdoc
     */
    public function onContentItem()
    {
        return $this->processRequest(Module::EVENT_CONTENT_ITEM);
    }

    /**
     * @inheritdoc
     */
    public function onError()
    {
        Module::getInstance()->trigger(Module::EVENT_ERROR, new ToolProviderEvent($this));

        return false;
    }

    /**
     * Whether debug messages explaining the cause of errors are to be returned to the tool consumer.
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->debugMode;
    }
}
