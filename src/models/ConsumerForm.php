<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\models;

use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use izumi\yii2lti\Module;
use Yii;
use yii\base\Model;

/**
 * Class ConsumerForm
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class ConsumerForm extends Model
{
    const SCENARIO_UPDATE = 'update';

    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $key;
    /**
     * @var mixed
     */
    public $newSecret;
    /**
     * @var mixed
     */
    public $enabled;
    /**
     * @var ToolConsumer
     */
    private $_consumer;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'key'], 'required'],
            ['name', 'string', 'min' => 3, 'max' => 50],
            ['key', 'string', 'min' => 3],
            ['newSecret', 'boolean', 'on' => [self::SCENARIO_UPDATE]],
            ['enabled', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('lti', 'Name'),
            'key' => Yii::t('lti', 'Key'),
            'newSecret' => Yii::t('lti', 'Generate new secret'),
            'enabled' => Yii::t('lti', 'Enabled'),
            'secret' => Yii::t('lti', 'Secret'),
        ];
    }

    public function setConsumer(ToolConsumer $consumer)
    {
        $this->scenario = self::SCENARIO_UPDATE;
        $this->_consumer = $consumer;
        $this->key = $consumer->getKey();
        $this->name = $consumer->name;
        $this->enabled = $consumer->enabled;
    }

    /**
     * @return bool|ToolConsumer
     */
    public function getConsumer()
    {
        if ($this->_consumer === null) {
            $c = new ToolConsumer(null, Module::getInstance()->toolProvider->dataConnector);
            $c->initialize();
            $this->_consumer = $c;
        }
        return $this->_consumer;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->getConsumer()->secret;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $consumer = $this->getConsumer();
        $consumer->setKey($this->key);
        $consumer->name = $this->name;
        $consumer->enabled = $this->enabled;
        if ($this->newSecret || $this->scenario === self::SCENARIO_DEFAULT) {
            $consumer->secret = sha1(Yii::$app->security->generateRandomKey(128));
        }
        try {
            $ok = $consumer->save();
        } catch (\PDOException $exception){
            $this->addError('key');
            return false;
        }
        return $ok;
    }
}
