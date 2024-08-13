<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Http\HttpMessage;
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
    const EVENT_CONFIGURE = 'configure';
    const EVENT_DASHBOARD = 'dashboard';
    const EVENT_CONTENT_ITEM = 'contentItem';
    const EVENT_CONTENT_ITEM_UPDATE = 'contentItemUpdate';
    const EVENT_SUBMISSION_REVIEW = 'submissionReview';
    const EVENT_ERROR = 'error';

    /**
     * @var ToolProvider|array
     */
    public array|ToolProvider $toolProvider = [];
    /**
     * @var Client|array|string
     */
    public string|Client|array $httpClient = [];
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     */
    public Connection|string|array $db = 'db';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $i18n = Yii::$app->i18n;
        if (!isset($i18n->translations['lti']) && !isset($i18n->translations['lti*'])) {
            $i18n->translations['lti'] = [
                'class' => '\yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en',
            ];
        }

        $this->db = Instance::ensure($this->db, Connection::class);

        $tpConfig = is_array($this->toolProvider) ? $this->toolProvider : [];
        $tpConfig['dataConnector'] = DataConnector::getDataConnector($this->db->getMasterPdo(), $this->db->tablePrefix);
        if (!isset($tpConfig['baseUrl'])) {
            $tpConfig['baseUrl'] = Yii::$app->getRequest()->getHostInfo();
        }
        $this->toolProvider = Instance::ensure($tpConfig, ToolProvider::class);

        $this->httpClient = Instance::ensure($this->httpClient, Client::class);
        HttpMessage::setHttpClient(new HttpClient());
    }
}
