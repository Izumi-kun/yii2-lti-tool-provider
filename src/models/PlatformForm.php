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
    public string $platformId = '';
    /**
     * @var string
     */
    public string $clientId = '';
    /**
     * @var string
     */
    public string $deploymentId = '';
    /**
     * @var string
     */
    public string $publicKey = '';
    /**
     * @var string
     */
    public string $publicKeysetUrl = '';
    /**
     * @var string
     */
    public string $authorizationServerId = '';
    /**
     * @var string
     */
    public string $authenticationUrl = '';
    /**
     * @var string
     */
    public string $accessTokenUrl = '';
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
            [['name'], 'required'],
            ['name', 'string', 'min' => 3, 'max' => 50],
            ['key', 'string', 'min' => 3],
            [['platformId', 'clientId', 'deploymentId', 'authorizationServerId', 'authenticationUrl', 'accessTokenUrl', 'publicKeysetUrl'], 'string', 'max' => 255, 'encoding' => '8bit'],
            [['publicKey'], 'trim'],
            [['publicKey'], 'string', 'max' => 65535, 'encoding' => '8bit'],
            [['publicKey'], 'publicKeyValidator'],
            ['newSecret', 'boolean', 'on' => [self::SCENARIO_UPDATE]],
            ['enabled', 'boolean'],
        ];
    }

    /**
     * @param string $attribute
     * @return void
     * @noinspection PhpUnused
     */
    public function publicKeyValidator(string $attribute): void
    {
        if (openssl_pkey_get_public($this->$attribute) === false) {
            $this->addError($attribute, Yii::t('lti', 'Public key is not valid'));
        }
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
            'platformId' => Yii::t('lti', 'Platform/Issuer ID'),
            'clientId' => Yii::t('lti', 'Client ID'),
            'deploymentId' => Yii::t('lti', 'Deployment ID'),
            'publicKey' => Yii::t('lti', 'Public key'),
            'publicKeysetUrl' => Yii::t('lti', 'Public keyset URL'),
            'authorizationServerId' => Yii::t('lti', 'Authorization server ID'),
            'authenticationUrl' => Yii::t('lti', 'Authentication request URL'),
            'accessTokenUrl' => Yii::t('lti', 'Access Token service URL'),
            'enabled' => Yii::t('lti', 'Enabled'),
            'secret' => Yii::t('lti', 'Secret'),
        ];
    }

    public function setPlatform(Platform $platform)
    {
        $this->scenario = self::SCENARIO_UPDATE;
        $this->_platform = $platform;
        $this->key = $platform->getKey() ?: '';
        $this->name = $platform->name ?: '';
        $this->platformId = $platform->platformId ?: '';
        $this->clientId = $platform->clientId ?: '';
        $this->deploymentId = $platform->deploymentId ?: '';
        $this->publicKey = $platform->rsaKey ?: '';
        $this->publicKeysetUrl = $platform->jku ?: '';
        $this->authorizationServerId = $platform->authorizationServerId ?: '';
        $this->authenticationUrl = $platform->authenticationUrl ?: '';
        $this->accessTokenUrl = $platform->accessTokenUrl ?: '';
        $this->enabled = $platform->enabled;
    }

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        if (!isset($this->_platform)) {
            $c = new Platform(Module::getInstance()->tool->dataConnector);
            $c->initialize();
            $this->_platform = $c;
        }
        return $this->_platform;
    }

    /**
     * @return string|null
     * @noinspection PhpUnused
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
        $platform->setKey($this->key ?: null);
        $platform->name = $this->name ?: null;
        $platform->platformId = $this->platformId ?: null;
        $platform->clientId = $this->clientId ?: null;
        $platform->deploymentId = $this->deploymentId ?: null;
        $platform->rsaKey = $this->publicKey ?: null;
        $platform->jku = $this->publicKeysetUrl ?: null;
        $platform->authorizationServerId = $this->authorizationServerId ?: null;
        $platform->authenticationUrl = $this->authenticationUrl ?: null;
        $platform->accessTokenUrl = $this->accessTokenUrl ?: null;
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
