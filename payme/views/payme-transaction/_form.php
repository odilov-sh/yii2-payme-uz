<?php

use soft\helpers\Html;
use soft\widget\kartik\ActiveForm;
use soft\widget\kartik\Form;

/* @var $this soft\web\View */
/* @var $model common\modules\payme\models\PaymeTransaction */

?>


    <?php $form = ActiveForm::begin(); ?>

    <?= Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
                  'transaction',
              'code',
              'state',
              'amount',
              'reason',
              'time',
              'cancel_time',
              'create_time',
              'perform_time',
        ]
    ]); ?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('site', 'Save'), ['visible' => !$this->isAjax ] ) ?>
    </div>

    <?php ActiveForm::end(); ?>

