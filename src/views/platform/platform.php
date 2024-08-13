<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

use izumi\yii2lti\models\PlatformForm;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model PlatformForm */

$isNew = $model->scenario === Model::SCENARIO_DEFAULT;
$id = $model->getPlatform()->getRecordId();

$this->title = $isNew ? Yii::t('lti', 'Create LTI Platform') : Yii::t('lti', 'Platform #{num}', ['num' => $id]);

$this->params['breadcrumbs'][] = ['label' => Yii::t('lti', 'LTI Platforms'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

?>

<?php $form = ActiveForm::begin() ?>
<div class="panel panel-default">
    <div class="panel-body">
        <?= $form->field($model, 'name')->textInput() ?>
        <?= $form->field($model, 'key')->textInput() ?>
        <?= $form->field($model, 'enabled')->checkbox() ?>
        <?php if (!$isNew): ?>
            <?= $form->field($model, 'secret')->textInput(['disabled' => true]) ?>
            <?= $form->field($model, 'newSecret')->checkbox() ?>
        <?php endif; ?>
        <div class="alert alert-info">
            <span class="glyphicon glyphicon-info-sign"></span>
            <?= Yii::t('lti', 'Launch URL:') ?>
            <strong><?= Html::encode(Url::to(['connect/index'], true)) ?></strong>
        </div>
    </div>
    <div class="panel-footer">
        <?= Html::submitButton($isNew ? Yii::t('lti', 'Create') : Yii::t('lti', 'Update'), ['class' => $isNew ? 'btn btn-success' : 'btn btn-warning']) ?>
        <?= Html::a(Yii::t('lti', 'Cancel'), ['index'], ['class' => 'btn btn-default']) ?>
        <?php if (!$isNew): ?>
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

