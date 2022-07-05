<?php
/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 07.05.2022, 10:16
 */

/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 07.05.2022, 9:58
 */

namespace common\modules\payme\behaviors;

use common\models\User;
use common\modules\payme\components\AbstractPayme;
use common\modules\payme\components\Payme;
use common\modules\payme\models\PaymeTransaction;
use Yii;
use yii\base\Behavior;

/**
 *
 * @property-read \common\modules\payme\behaviors\PaymeRequest $request
 */
class PaymeAccess extends Behavior
{

    /**
     * @var Payme|AbstractPayme
     */
    public $owner;

    /**
     * @return \common\modules\payme\behaviors\PaymeRequest
     */
    public function getRequest()
    {
        return $this->owner->request;
    }

    /**
     * @return bool
     */
    public function hasAuth(): bool
    {
        $username = Yii::$app->request->getAuthUser();
        $password = Yii::$app->request->getAuthPassword();
        $data = $this->owner->data;

        $key = $data->getKey();
        return $username == $data->login && $password == $key;
    }


    /**
     * Transaksiyani tekshiradi timeoutga qarab
     *
     * @param $created_time
     * @return bool
     */
    public function checkTimeout($created_time)
    {
        return $this->owner->microtime() <= ($created_time + $this->owner->timeout);
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function hasUser()
    {
        return User::find()->andWhere(['id' => $this->request->getUserId()])->exists();
    }

    /**
     * @return bool
     */
    public function isWrongAmount()
    {
        $amount = $this->request->getAmount();
        return $amount < PaymeData::MIN_AMOUNT || $amount > PaymeData::MAX_AMOUNT;
    }

    /**
     *
     * Check if waiting transaction exists for user
     *
     * @param $userId int User ID
     * @param $transId mixed Transaction ID
     *
     * @return bool
     */
    public function isPerformingAnotherTransaction($userId, $transId)
    {
        return PaymeTransaction::find()
            ->andWhere(['user_id' => $userId, 'state' => PaymeTransaction::STATE_WAITING])
            ->andWhere(['!=', 'transaction', $transId ])
            ->exists();
    }

}
