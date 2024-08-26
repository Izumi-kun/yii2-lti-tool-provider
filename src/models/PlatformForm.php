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
    public string $secret = '';
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
            ['key', 'platformKeyValidator'],
            [['platformId', 'clientId', 'deploymentId', 'authorizationServerId', 'authenticationUrl', 'accessTokenUrl', 'publicKeysetUrl'], 'string', 'max' => 255, 'encoding' => '8bit'],
            ['platformId', 'platformIdValidator'],
            ['secret', 'string', 'max' => 1024, 'encoding' => '8bit'],
            ['publicKey', 'trim'],
            ['publicKey', 'string', 'max' => 65535, 'encoding' => '8bit'],
            ['publicKey', 'publicKeyValidator'],
            ['newSecret', 'boolean'],
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
            $this->addError($attribute, Yii::t('lti', 'Public key is not valid.'));
        }
    }

    /**
     * @param string $attribute
     * @return void
     * @noinspection PhpUnused
     */
    public function platformKeyValidator(string $attribute): void
    {
        $key = $this->$attribute;
        if (!$key) {
            return;
        }
        $platform = Platform::fromConsumerKey($key, Module::getInstance()->tool->dataConnector);
        if ($platform->created && $this->getPlatform()->getRecordId() !== $platform->getRecordId()) {
            $this->addError($attribute, Yii::t('lti', 'Consumer key has already been taken.'));
        }
    }

    /**
     * @param string $attribute
     * @return void
     * @noinspection PhpUnused
     */
    public function platformIdValidator(string $attribute): void
    {
        if (!$this->platformId || !$this->clientId || !$this->deploymentId) {
            return;
        }
        $platform = Platform::fromPlatformId($this->platformId, $this->clientId, $this->deploymentId, Module::getInstance()->tool->dataConnector);
        if ($platform->created && $this->getPlatform()->getRecordId() !== $platform->getRecordId()) {
            $this->addError($attribute, Yii::t('lti', 'The combination of platform, client and deployment IDs has already been taken.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => Yii::t('lti', 'Name'),
            'key' => Yii::t('lti', 'Consumer key'),
            'newSecret' => Yii::t('lti', 'Generate new shared secret'),
            'platformId' => Yii::t('lti', 'Platform/Issuer ID'),
            'clientId' => Yii::t('lti', 'Client ID'),
            'deploymentId' => Yii::t('lti', 'Deployment ID'),
            'publicKey' => Yii::t('lti', 'Public key'),
            'publicKeysetUrl' => Yii::t('lti', 'Public keyset URL'),
            'authorizationServerId' => Yii::t('lti', 'Authorization server ID'),
            'authenticationUrl' => Yii::t('lti', 'Authentication request URL'),
            'accessTokenUrl' => Yii::t('lti', 'Access Token service URL'),
            'enabled' => Yii::t('lti', 'Enabled'),
            'secret' => Yii::t('lti', 'Shared secret'),
        ];
    }

    public function setPlatform(Platform $platform)
    {
        $this->_platform = $platform;
        $this->key = $platform->getKey() ?: '';
        $this->secret = $platform->secret ?: '';
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
            $this->_platform = new Platform(Module::getInstance()->tool->dataConnector);
        }
        return $this->_platform;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $platform = $this->getPlatform();
        $platform->name = $this->name ?: null;
        $platform->enabled = (bool)$this->enabled;
        // LTI 1.0/1.1/1.2/2.0
        $platform->setKey($this->key ?: null);
        if ($this->newSecret) {
            $this->secret = sha1(Yii::$app->security->generateRandomKey(128));
        }
        $platform->secret = $this->secret ?: null;
        // LTI 1.3
        $platform->platformId = $this->platformId ?: null;
        $platform->clientId = $this->clientId ?: null;
        $platform->deploymentId = $this->deploymentId ?: null;
        $platform->rsaKey = $this->publicKey ?: null;
        $platform->jku = $this->publicKeysetUrl ?: null;
        $platform->authorizationServerId = $this->authorizationServerId ?: null;
        $platform->authenticationUrl = $this->authenticationUrl ?: null;
        $platform->accessTokenUrl = $this->accessTokenUrl ?: null;
        try {
            $ok = $platform->save();
        } catch (PDOException){
            $this->addError('key');
            return false;
        }
        return $ok;
    }

    /**
     * Platform is ready for LTI 1.3
     * @param Platform $platform
     * @return bool
     */
    public static function isPlatform1p3Ready(Platform $platform): bool
    {
        return $platform->platformId
            && $platform->clientId
            && $platform->deploymentId
            && ($platform->rsaKey || $platform->jku)
            && $platform->authorizationServerId
            && $platform->authenticationUrl
            && $platform->accessTokenUrl;
    }

    /**
     * Platform is ready for LTI 1.0/1.1/1.2/2.0
     * @param Platform $platform
     * @return bool
     */
    public static function isPlatform1p0Ready(Platform $platform): bool
    {
        return $platform->getKey()
            && $platform->secret;
    }
}
