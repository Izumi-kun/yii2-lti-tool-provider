<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\models;

use ceLTIc\LTI\Platform;
use izumi\yii2lti\Module;
use PDOException;
use Yii;
use yii\base\Model;

/**
 * Class PlatformForm
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class PlatformForm extends Model
{
    const SCENARIO_UPDATE = 'update';

    /**
     * @var string
     */
    public string $name = '';
    /**
     * @var string
     */
    public string $key = '';
    /**
     * @var string
     */
    public string $newSecret = '0';
    /**
     * @var string
     */
    public string $enabled = '0';
    /**
     * @var Platform
     */
    private Platform $_platform;

    /**
     * @inheritdoc
     */
    public function rules(): array
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
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('lti', 'Name'),
            'key' => Yii::t('lti', 'Key'),
            'newSecret' => Yii::t('lti', 'Generate new secret'),
            'enabled' => Yii::t('lti', 'Enabled'),
            'secret' => Yii::t('lti', 'Secret'),
        ];
    }

    public function setPlatform(Platform $platform)
    {
        $this->scenario = self::SCENARIO_UPDATE;
        $this->_platform = $platform;
        $this->key = $platform->getKey();
        $this->name = $platform->name;
        $this->enabled = $platform->enabled;
    }

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        if (!isset($this->_platform)) {
            $c = new Platform(Module::getInstance()->toolProvider->dataConnector);
            $c->initialize();
            $this->_platform = $c;
        }
        return $this->_platform;
    }

    /**
     * @return string|null
     */
    public function getSecret(): ?string
    {
        return $this->getPlatform()->secret;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $platform = $this->getPlatform();
        $platform->setKey($this->key);
        $platform->name = $this->name;
        $platform->enabled = (bool)$this->enabled;
        if ($this->newSecret || $this->scenario === self::SCENARIO_DEFAULT) {
            $platform->secret = sha1(Yii::$app->security->generateRandomKey(128));
        }
        try {
            $ok = $platform->save();
        } catch (PDOException){
            $this->addError('key');
            return false;
        }
        return $ok;
    }
}
