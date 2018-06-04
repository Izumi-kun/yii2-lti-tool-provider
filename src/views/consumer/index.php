<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

use IMSGlobal\LTI\ToolProvider\ToolConsumer;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $consumers ToolConsumer[] */

$this->title = Yii::t('lti', 'LTI Consumers');

$this->params['breadcrumbs'][] = ['label' => $this->title];

?>

<div class="row">
    <?php foreach ($consumers as $consumer): ?>
        <div class="col-lg-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <span class="panel-title">
                        <strong><?= Html::a(Html::encode($consumer->name), ['update', 'id' => $consumer->getRecordId()], ['class' => '']) ?></strong>
                    </span>
                </div>
                <div class="panel-footer small text-muted">
                    <div>
                        <?= Yii::t('lti', 'Status:') ?>
                        <span class="<?= $consumer->getIsAvailable() ? 'text-success' : 'text-muted' ?>">
                            <?php if ($consumer->getIsAvailable()): ?>
                                <span class="glyphicon glyphicon-play"></span>
                                <strong><?= Yii::t('lti', 'Enabled') ?></strong>
                            <?php else: ?>
                                <span class="glyphicon glyphicon-pause"></span>
                                <?= Yii::t('lti', 'Disabled') ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div>
                        <?= Yii::t('lti', 'Key:') ?>
                        <?= $consumer->getKey() ?>
                    </div>
                    <div>
                        <?= Yii::t('lti', 'Created:') ?>
                        <?= Yii::$app->formatter->asDatetime($consumer->created) ?>
                    </div>
                    <div>
                        <?= Yii::t('lti', 'Updated:') ?>
                        <?= Yii::$app->formatter->asDatetime($consumer->updated) ?>
                    </div>
                    <div>
                        <?= Yii::t('lti', 'Last access:') ?>
                        <?= Yii::$app->formatter->asDate($consumer->lastAccess) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<a href="<?= Url::to(['create']) ?>" class="btn btn-success">
    <span class="glyphicon glyphicon-plus-sign"></span>
    <?= Yii::t('lti', 'Create') ?>
</a>
