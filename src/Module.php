<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Enum\ServiceAction;
use ceLTIc\LTI\Http\HttpMessage;
use ceLTIc\LTI\Outcome;
use ceLTIc\LTI\Tool as BaseTool;
use ceLTIc\LTI\UserResult;
use Yii;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Url;
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
     * @var Tool|array
     */
    public array|Tool $tool = [];
    /**
     * @var Client|array|string
     */
    public string|Client|array $httpClient = [];
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     */
    public Connection|string|array $db = 'db';
    /**
     * @var string
     */
    public string $lti1p3SignatureMethod = 'RS256';

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

        $toolConfig = is_array($this->tool) ? $this->tool : [];
        $toolConfig['dataConnector'] = DataConnector::getDataConnector($this->db->getMasterPdo(), $this->db->tablePrefix);
        if (!isset($toolConfig['baseUrl'])) {
            $toolConfig['baseUrl'] = Yii::$app->getRequest()->getHostInfo();
        }
        if (!array_key_exists('jku', $toolConfig)) {
            $toolConfig['jku'] = Url::to($this->getUniqueId() . '/tool/jwks', true);
        }
        $this->tool = Instance::ensure($toolConfig, Tool::class);

        if ($this->tool->rsaKey && openssl_pkey_get_private($this->tool->rsaKey) === false) {
            $this->tool->rsaKey = null;
            Yii::warning('rsaKey is not valid private key');
        }

        $this->httpClient = Instance::ensure($this->httpClient, Client::class);
        HttpMessage::setHttpClient(new HttpClient());
        BaseTool::$defaultTool = $this->tool;
    }

    /**
     * Load the user from the database.
     * @param int $id
     * @return UserResult|null
     */
    public function findUserById(int $id): ?UserResult
    {
        $user = UserResult::fromRecordId($id, $this->tool->dataConnector);
        if (!$user->getResourceLink()) {
            return null;
        }
        return $user;
    }

    /**
     * Perform an Outcomes service request.
     * @param ServiceAction $action
     * @param Outcome $ltiOutcome
     * @param UserResult $user
     * @return bool
     */
    public function doOutcomesService(ServiceAction $action, Outcome $ltiOutcome, UserResult $user): bool
    {
        $resourceLink = $user->getResourceLink();
        if (!$resourceLink) {
            return false;
        }
        $this->tool->signatureMethod = $resourceLink->getPlatform()->ltiVersion === LtiVersion::V1P3 ? $this->lti1p3SignatureMethod : 'HMAC-SHA1';
        return $resourceLink->doOutcomesService($action, $ltiOutcome, $user);
    }
}
