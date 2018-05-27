<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector_pdo;
use Yii;
use yii\db\Connection;
use yii\di\Instance;

/**
 * ToolProvider
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ToolProvider extends \IMSGlobal\LTI\ToolProvider\ToolProvider
{
    /**
     * @var string|Connection
     */
    public $db = 'db';

    public function __construct()
    {
        $this->db = Instance::ensure($this->db, Connection::class);
        $dataConnector = new DataConnector_pdo($this->db->getMasterPdo(), $this->db->tablePrefix);

        parent::__construct($dataConnector);

        if (empty($this->baseUrl)) {
            $this->baseUrl = Yii::$app->getRequest()->getHostInfo();
        }
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
