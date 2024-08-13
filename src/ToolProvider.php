<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti;

use ceLTIc\LTI\Tool;
use Yii;
use yii\base\Configurable;

/**
 * ToolProvider
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ToolProvider extends Tool implements Configurable
{

    public function __construct($config = [])
    {
        Yii::configure($this, $config);

        parent::__construct($this->dataConnector);
    }

    /**
     * @param string $eventName
     */
    protected function processRequest(string $eventName): void
    {
        Yii::debug("Action requested: '$eventName'", __METHOD__);
        $event = new ToolProviderEvent($this);
        Module::getInstance()->trigger($eventName, $event);
        if (!$event->handled) {
            Yii::debug("Message type not supported: {$_POST['lti_message_type']}", __METHOD__);
            $this->ok = false;
            $this->reason = "Message type not supported: {$_POST['lti_message_type']}";
        }
    }

    /**
     * @inheritdoc
     */
    protected function onLaunch(): void
    {
        $this->processRequest(Module::EVENT_LAUNCH);
    }

    /**
     * @inheritdoc
     */
    protected function onConfigure(): void
    {
        $this->processRequest(Module::EVENT_CONFIGURE);
    }

    /**
     * @inheritdoc
     */
    protected function onDashboard(): void
    {
        $this->processRequest(Module::EVENT_DASHBOARD);
    }

    /**
     * @inheritdoc
     */
    protected function onContentItem(): void
    {
        $this->processRequest(Module::EVENT_CONTENT_ITEM);
    }

    /**
     * @inheritdoc
     */
    protected function onContentItemUpdate(): void
    {
        $this->processRequest(Module::EVENT_CONTENT_ITEM_UPDATE);
    }

    /**
     * @inheritdoc
     */
    protected function onSubmissionReview(): void
    {
        $this->processRequest(Module::EVENT_SUBMISSION_REVIEW);
    }

    /**
     * @inheritdoc
     */
    protected function onError(): void
    {
        $this->message = Yii::t('lti', 'Sorry, there was an error connecting you to the application.');
        Module::getInstance()->trigger(Module::EVENT_ERROR, new ToolProviderEvent($this));
        $this->ok = false;
    }

    /**
     * @param string $value HTML to be displayed on a successful completion of the request.
     */
    public function setOutput(string $value)
    {
        $this->output = $value;
    }

    /**
     * @param string $value HTML to be displayed on an unsuccessful completion of the request and no return URL is available.
     */
    public function setErrorOutput(string $value)
    {
        $this->errorOutput = $value;
    }
}
