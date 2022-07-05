<?php


/* @var $this soft\web\View */
/* @var $model common\modules\payme\models\PaymeTransaction */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Payme Transactions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

    <?= \soft\widget\bs4\DetailView::widget([
        'model' => $model,
        'attributes' => [
              'id', 
              'owner_id', 
              'transaction', 
              'code', 
              'state', 
              'amount', 
              'reason', 
              'time', 
              'cancel_time', 
              'create_time', 
              'perform_time', 
'created_at',
'createdBy.fullname',
'updated_at',
'updatedBy.fullname'        ],
    ]) ?>
