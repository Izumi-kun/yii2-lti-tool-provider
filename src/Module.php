<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use IMSGlobal\LTI\HTTPMessage;
use IMSGlobal\LTI\ToolProvider\DataConnector\DataConnector_pdo;
use Yii;
use yii\db\Connection;
use yii\di\Instance;
use yii\httpclient\Client;

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
     * @var ToolProvider|array
     */
    public $toolProvider = [];
    /**
     * @var Client|array|string
     */
    public $httpClient = [];
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     */
    public $db = 'db';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->db = Instance::ensure($this->db, Connection::className());

        $tpConfig = is_array($this->toolProvider) ? $this->toolProvider : [];
        $tpConfig['dataConnector'] = new DataConnector_pdo($this->db->getMasterPdo(), $this->db->tablePrefix);
        if (!isset($tpConfig['baseUrl'])) {
            $tpConfig['baseUrl'] = Yii::$app->getRequest()->getHostInfo();
        }
        $this->toolProvider = Instance::ensure($tpConfig, '\izumi\yii2lti\ToolProvider');

        $this->httpClient = Instance::ensure($this->httpClient, Client::className());
        HTTPMessage::setHttpClient(new HttpClient());
    }

    /**
     * Process an incoming request
     * @return string Output to be displayed
     */
    public function handleRequest()
    {
        ob_start();
        ob_implicit_flush(false);
        $this->toolProvider->handleRequest();
        $result = ob_get_clean();
        return $result;
    }
}
