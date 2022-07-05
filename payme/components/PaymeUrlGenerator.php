<?php
/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 14.05.2022, 12:10
 */

namespace common\modules\payme\components;

use common\modules\payme\behaviors\PaymeData;
use common\modules\user\models\User;
use Yii;
use yii\base\Component;
use yii\base\Model;

/**
 *
 * Generates Get Url for Payme
 * @see https://developer.help.paycom.uz/uz/initsializatsiya-platezhey/otpravka-cheka-po-metodu-get
 *
 * @property mixed $userId
 * @property-read string $paramsAsString
 * @property-read \common\modules\payme\behaviors\PaymeData $data
 */
class PaymeUrlGenerator extends Model
{

    /**
     * @var int Pul miqdori (tiyinda).
     */
    public $amount;

    public $baseUrl = 'https://checkout.paycom.uz';

    public $userId;

    public function init()
    {
        if ($this->userId === null) {
            $this->userId = Yii::$app->user->id;
        }
        parent::init();
    }

    public function rules()
    {
        return [
            [['amount', 'userId'], 'required'],
            ['amount', 'integer'],
            ['amount', 'checkAmount'],
            ['userId', 'checkUser'],
        ];
    }

    /**
     * @param string $attribute
     */
    public function checkAmount($attribute)
    {
        $amount = (int)$this->amount / 100; //convert to sum from tiyin
        if ($amount < PaymeData::MIN_AMOUNT || $amount > PaymeData::MAX_AMOUNT){
            $this->addError($attribute, "Pul miqdori noto'g'ri");
        }
    }

    /**
     * @param $attribute
     */
    public function checkUser($attribute)
    {
        if (!User::find()->active()->andWhere(['id' => $this->userId])->exists()) {
            $this->addError($attribute, "Foydalanuvchi topilmadi!");
        }
    }

    public function behaviors()
    {
        return [
            'data' => [
                'class' => PaymeData::class,
            ]
        ];
    }

    /**
     * @return PaymeData
     */
    public function getData()
    {
        return $this->getBehavior('data');
    }


    public function generateUrl()
    {
        $params = $this->getParamsAsString();
        return $this->baseUrl . '/' . base64_encode($params);
    }

    /**
     * @return array
     */
    public function generateParams()
    {
        return [
            'm' => $this->data->merchantId,
            'ac.id' => $this->userId,
            'a' => $this->amount,
            'l' => Yii::$app->language,
            'c' => Yii::$app->request->hostInfo,
            'cr' => 'UZS',
        ];
    }

    /**
     * @return string
     */
    public function getParamsAsString()
    {
        $params = $this->generateParams();

        $result = '';

        foreach ($params as $key => $value) {
            $result .= $key . '=' . $value . ';';
        }
        return rtrim($result, ';');
    }

}
