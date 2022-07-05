<?php
/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 07.05.2022, 11:14
 */

namespace common\modules\payme\models;

use common\models\User;
use common\modules\userbalance\models\UserPayment;
use Yii;

/**
 * This is the model class for table "payme_uz_transaction".
 *
 * @property int $id
 * @property int $user_id [int(11)]
 * @property int $user_payment_id [int(11)]
 * @property string $transaction
 * @property int|null $code
 * @property int $state
 * @property int $amount
 * @property int|null $reason
 * @property int $time
 * @property int $cancel_time
 * @property int $create_time
 * @property int $perform_time
 *
 *
 * @property-read User $user
 * @property-read bool $isPerformed
 * @property-read bool $isWaiting
 * @property-read bool $isCanceled
 * @property-read UserPayment $userPayment
 * @property-read User $owner
 */
class PaymeTransaction extends \yii\db\ActiveRecord
{

    /**
     * Transaksiya kutish rejimida va to'lovni amalga oshirish mn.
     */
    const STATE_WAITING = 1;

    /**
     * Transaksiya uchun to'lov amalga oshirib bo'lingan.
     */
    const STATE_PERFORMED = 2;

    /**
     * Transaksiya to'lov qilinmasdan avval bekor qilingan.
     */
    const STATE_CANCELED_BEFORE_PERFORMING = -1;

    /**
     * Transaksiya to'lov qilinganidan keyin bekor qilingan.
     */
    const STATE_CANCELED_AFTER_PERFORMED = -2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payme_uz_transaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'transaction', 'state', 'amount'], 'required'],
            [['user_id', 'code', 'user_payment_id', 'state', 'amount', 'reason', 'time', 'cancel_time', 'create_time', 'perform_time'], 'integer'],
            [['transaction'], 'string', 'max' => 25],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner_id' => 'Owner ID',
            'transaction' => 'Transaction',
            'code' => 'Code',
            'state' => 'State',
            'amount' => 'Amount',
            'reason' => 'Reason',
            'time' => 'Time',
            'cancel_time' => 'Cancel Time',
            'create_time' => 'Create Time',
            'perform_time' => 'Perform Time',
        ];
    }

    /**
     * @return \common\models\query\UserQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPayment()
    {
        return $this->hasOne(UserPayment::class, ['transaction_id' => 'id'])->andWhere(['user_payment.type_id' => UserPayment::TYPE_PAYME]);
    }

    /**
     * @param $transId mixed
     * @return \common\modules\payme\models\PaymeTransaction|null
     */
    public function getByTransId($transId)
    {
        return static::findOne(['transaction' => $transId]);
    }

    /**
     *
     * Updates existing transaction
     * @param array $fields
     * @return bool
     */
    public function updateTransaction(array $fields)
    {
        $this->setAttributes($fields);
        return $this->save();
    }

    /**
     *
     * Performs transaction.
     * @return bool
     * @throws \yii\db\Exception
     */
    public function performTransaction()
    {


        $dbTransaction = Yii::$app->db->beginTransaction();

        $this->state = self::STATE_PERFORMED; // mark as performed
        $this->perform_time = $this->microtime(); // set perform time in milliseconds

        try {
            if ($this->save()) {
                $user = $this->user;
                if ($user) {
                    if ($user->fillBalance($this->amount, UserPayment::TYPE_PAYME, $this->id)) {
                        $dbTransaction->commit();
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
        }
        $dbTransaction->rollBack();
        return false;
    }

    /**
     * @param $reason mixed
     * @return bool
     * @throws \yii\db\Exception
     */
    public function cancelTransaction($reason = null)
    {

        // if transaction already canceled, return true
        if ($this->getIsCanceled()) {
            return true;
        }

        // if transaction is waiting to perform, just cancel it
        if ($this->state == self::STATE_WAITING) {

            $cancelTime = $this->microtime();
            $this->setAttributes([
                "state" => self::STATE_CANCELED_BEFORE_PERFORMING,
                "cancel_time" => $cancelTime,
                "reason" => $reason
            ]);

            return $this->save();

        }


        // if transaction is already performed ...

        if ($this->state == self::STATE_PERFORMED) {

            $user = $this->user;

            // if user doesnot have enough money,  transaction can not be canceled
            if ($user->balance < $this->amount) {
                return false;
            }

            $dbTransaction = Yii::$app->db->beginTransaction();
            $this->state = self::STATE_CANCELED_AFTER_PERFORMED;
            $this->cancel_time = $this->microtime();
            $this->reason = $reason;
            if ($this->save() && $this->userPayment->delete()) {
                $dbTransaction->commit();
                return true;
            }
            $dbTransaction->rollBack();
            return false;

        }

        return false;

    }


    /**
     * @return bool
     */
    public function getIsWaiting(): bool
    {
        return $this->state == self::STATE_WAITING;
    }

    /**
     * @return bool
     */
    public function getIsPerformed(): bool
    {
        return $this->state == self::STATE_PERFORMED;
    }

    /**
     * @return bool
     */
    public function getIsCanceled(): bool
    {
        return $this->state == self::STATE_CANCELED_AFTER_PERFORMED || $this->state == self::STATE_CANCELED_BEFORE_PERFORMING;
    }

    private function microtime()
    {
        return time() * 1000;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
