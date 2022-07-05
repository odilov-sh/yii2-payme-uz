<?php

use common\modules\payme\models\PaymeTransaction;

/* @var $this soft\web\View */
/* @var $searchModel common\modules\payme\models\search\PaymeTransactionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Payme Transactions';
$this->params['breadcrumbs'][] = $this->title;
$this->registerAjaxCrudAssets();
?>
<?= \soft\grid\GridView::widget([
    'id' => 'crud-datatable',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'toolbarTemplate' => '{create}{refresh}',
    'toolbarButtons' => [
        'create' => [
            /** @see soft\widget\button\Button for other configurations */
            'modal' => true,
        ]
    ],
    'columns' => [
//                    'id',
        'owner.fullname',
        'transaction',
//        'code',
//        'state',
        'amount',
        //'reason',
        //'time',
        //'cancel_time',
        //'create_time',
        //'perform_time',
        'actionColumn' => [
            'viewOptions' => [
                'role' => 'modal-remote',
            ],
            'updateOptions' => [
                'role' => 'modal-remote',
            ],
        ],
    ],
]); ?>
    