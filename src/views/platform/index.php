<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

use ceLTIc\LTI\Platform;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $platforms Platform[] */

$this->title = Yii::t('lti', 'LTI Platforms');

$this->params['breadcrumbs'][] = ['label' => $this->title];

?>

<div class="row">
    <?php foreach ($platforms as $platform): ?>
        <div class="col-lg-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <span class="panel-title">
                        <strong><?= Html::a(Html::encode($platform->name), ['update', 'id' => $platform->getRecordId()], ['class' => '']) ?></strong>
                    </span>
                </div>
                <div class="panel-footer small text-muted">
                    <div>
                        <?= Yii::t('lti', 'Status:') ?>
                        <span class="<?= $platform->getIsAvailable() ? 'text-success' : 'text-muted' ?>">
                            <?php if ($platform->getIsAvailable()): ?>
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
                        <?= $platform->getKey() ?>
                    </div>
                    <div>
                        <?= Yii::t('lti', 'Created:') ?>
                        <?= Yii::$app->formatter->asDatetime($platform->created) ?>
                    </div>
                    <div>
                        <?= Yii::t('lti', 'Updated:') ?>
                        <?= Yii::$app->formatter->asDatetime($platform->updated) ?>
                    </div>
                    <div>
                        <?= Yii::t('lti', 'Last access:') ?>
                        <?= Yii::$app->formatter->asDate($platform->lastAccess) ?>
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
