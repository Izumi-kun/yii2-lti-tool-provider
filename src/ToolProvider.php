<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector_pdo;
use Yii;

/**
 * ToolProvider
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ToolProvider extends \IMSGlobal\LTI\ToolProvider\ToolProvider
{
    public function __construct()
    {
        $db = Yii::$app->getDb();
        $dataConnector = new DataConnector_pdo($db->getMasterPdo(), $db->tablePrefix);

        parent::__construct($dataConnector);
    }

    /**
     * @param string $eventName
     * @return bool
     */
    protected function processRequest(string $eventName): bool
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
}
