<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

use izumi\yii2lti\models\ConsumerForm;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model ConsumerForm */

$isNew = $model->scenario === ConsumerForm::SCENARIO_DEFAULT;
$id = $model->getConsumer()->getRecordId();

$this->title = $isNew ? Yii::t('lti', 'Create LTI Consumer') : Yii::t('lti', 'Consumer #{num}', ['num' => $id]);

$this->params['breadcrumbs'][] = ['label' => Yii::t('lti', 'LTI Consumers'), 'url' => ['index']];
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
    </div>
    <div class="panel-footer">
        <?= Html::submitButton($isNew ? Yii::t('lti', 'Create') : Yii::t('lti', 'Update'), ['class' => $isNew ? 'btn btn-success' : 'btn btn-warning']) ?>
        <?= Html::a(Yii::t('lti', 'Cancel'), ['index'], ['class' => 'btn btn-default']) ?>
        <?php if (!$isNew): ?>
            <div class="pull-right">
                <?= Html::a(Yii::t('lti', 'Delete'), "#", ['class' => 'btn btn-danger', 'onclick' => 'confirm() && document.forms["delete"].submit();return false;']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php ActiveForm::end() ?>

<?= Html::beginForm(['delete', 'id' => $id], 'post', ['name' => 'delete']) ?>
<?= Html::endForm() ?>
