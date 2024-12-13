<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

use ceLTIc\LTI\Jwt\Jwt;
use izumi\yii2lti\models\PlatformForm;
use izumi\yii2lti\Module;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model PlatformForm */

$tool = Module::getInstance()->tool;
$platform = $model->getPlatform();
$isNew = !$platform->created;
$id = $platform->getRecordId();

$this->title = $isNew ? Yii::t('lti', 'New LTI Platform') : Yii::t('lti', 'Platform #{num}', ['num' => $id]);

$this->params['breadcrumbs'][] = ['label' => Yii::t('lti', 'LTI Platforms'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

?>

<?php $form = ActiveForm::begin() ?>
<?= $form->errorSummary([$model]) ?>
<div class="panel panel-primary">
    <div class='panel-heading'>
        <h2 class="panel-title"><?= Html::encode($this->title) ?></h2>
    </div>
    <div class="panel-body">
        <?= $form->field($model, 'name')->textInput() ?>
        <div class="panel <?= PlatformForm::isPlatform1p0Ready($platform) ? 'panel-success' : 'panel-info' ?>">
            <div class='panel-heading'>
                <strong>LTI 1.0/1.1/1.2/2.0</strong>
            </div>
            <div class='panel-body'>
                <?= $form->field($model, 'key')->textInput() ?>
                <?= $form->field($model, 'secret')->textInput() ?>
                <?= $form->field($model, 'newSecret')->checkbox() ?>
            </div>
        </div>
        <?php if ($tool->rsaKey) : ?>
            <div class='panel <?= PlatformForm::isPlatform1p3Ready($platform) ? 'panel-success' : 'panel-info' ?>'>
                <div class='panel-heading'>
                    <strong>LTI 1.3</strong>
                </div>
                <div class='panel-body'>
                    <?= $form->field($model, 'platformId')->textInput() ?>
                    <?= $form->field($model, 'clientId')->textInput() ?>
                    <?= $form->field($model, 'deploymentId')->textInput() ?>
                    <?= $form->field($model, 'authorizationServerId')->textInput() ?>
                    <?= $form->field($model, 'authenticationUrl')->textInput() ?>
                    <?= $form->field($model, 'accessTokenUrl')->textInput() ?>
                    <?= $form->field($model, 'publicKey')->textarea(['rows' => $model->publicKey ? substr_count($model->publicKey, "\n") + 1 : 2]) ?>
                    <?= $form->field($model, 'publicKeysetUrl')->textInput() ?>
                </div>
            </div>
        <?php endif; ?>
        <?= $form->field($model, 'enabled')->checkbox() ?>
    </div>
    <div class="panel-footer">
        <?= Html::submitButton($isNew ? Yii::t('lti', 'Create') : Yii::t('lti', 'Update'), ['class' => $isNew ? 'btn btn-success' : 'btn btn-warning']) ?>
        <?= Html::a(Yii::t('lti', 'Cancel'), ['index'], ['class' => 'btn btn-default']) ?>
        <?php if (!$isNew) : ?>
            <div class="pull-right">
                <?= Html::a(Yii::t('lti', 'Delete'), ['delete', 'id' => $id], [
                    'class' => 'btn btn-danger',
                    'data' => ['method' => 'post', 'confirm' => Yii::t('lti', 'Are you sure you want to delete this platform?')],
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php ActiveForm::end() ?>

<div class='panel panel-info'>
    <div class='panel-heading'>
        <h2 class="panel-title"><?= Yii::t('lti', 'Tool Details') ?></h2>
    </div>
    <div class='panel-body'>
        <p>
            <?= Yii::t('lti', 'Launch URL:') ?>
            <strong><?= Html::encode(Url::to(['tool/connect'], true)) ?></strong>
        </p>
        <?php if ($tool->rsaKey) : ?>
            <p>
                <?= Yii::t('lti', 'Public keyset URL:') ?>
                <strong><?= Html::encode($tool->jku) ?></strong>
            </p>
            <div>
                <?= Yii::t('lti', 'Public key:') ?>
                <pre><?= Html::encode(Jwt::getJwtClient()::getPublicKey($tool->rsaKey)) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</div>

