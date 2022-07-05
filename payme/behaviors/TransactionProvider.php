<?php
/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 09.05.2022, 8:24
 */

namespace common\modules\payme\behaviors;

use common\modules\payme\models\PaymeTransaction;

/**
 * Bu kontract klass, agar shu package boshqa frameworkda ishlatilsa
 * Unda mana shu interferda bulishi kk
 *
 * Class TransactionProvider
 * @package app\components\payme\contracts
 */
class TransactionProvider extends \yii\base\Behavior
{

    /**
     * Find by transaction id from transactions table
     *
     * @param $transId
     * @return PaymeTransaction|null
     */
    public function getByTransId($transId)
    {
        return PaymeTransaction::findOne(['transaction' => $transId]);
    }


    /**
     * Add new transaction
     *
     * @param array $fields
     * @return bool
     */
    public function insertTransaction(array $fields)
    {
        $transaction = new PaymeTransaction();
        $transaction->setAttributes($fields);
        return $transaction->save() ? $transaction : false;
    }

}
