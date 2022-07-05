<?php


/* @var $this soft\web\View */
/* @var $model common\modules\payme\models\PaymeTransaction */

$this->title = Yii::t('site', 'Create a new');
$this->params['breadcrumbs'][] = ['label' => 'Payme Transactions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
    'model' => $model,
]) ?>
