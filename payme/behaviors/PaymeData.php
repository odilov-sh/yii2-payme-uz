<?php
/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 07.05.2022, 10:16
 */

/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 07.05.2022, 8:56
 */

namespace common\modules\payme\behaviors;

use common\modules\payme\components\AbstractPayme;
use common\modules\payme\components\Payme;
use Yii;
use yii\base\Behavior;
use yii\base\Component;

/**
 *
 * @property-read string $secretKey
 * @property-read string $merchantId
 * @property-read string $testSecretKey
 * @property-read string $login
 * @property-read bool $testMode
 * @property-read string $key
 */
class PaymeData extends Behavior
{

    /**
     * Minimal summa. So'mda
     */
    const MIN_AMOUNT = 1000;

    /**
     * Maximal summa. So'mda
     */
    const MAX_AMOUNT = 10000000;

    /**
     * @var Payme|AbstractPayme
     */
    public $owner;

    private $_merchant_id;

    private $_secretKey;

    private $_testSecretKey;

    private $_login;

    private $_testMode;

    /**
     * @return string
     */
    public function getMerchantId()
    {
        if ($this->_merchant_id === null) {
            $this->_merchant_id = Yii::$app->params['payme']['merchant_id'];
        }
        return $this->_merchant_id;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        if ($this->_secretKey === null) {
            $this->_secretKey = Yii::$app->params['payme']['secret_key'];
        }
        return $this->_secretKey;
    }

    /**
     * @return string
     */
    public function getTestSecretKey()
    {
        if ($this->_testSecretKey === null) {
            $this->_testSecretKey = Yii::$app->params['payme']['test_secret_key'];
        }
        return $this->_testSecretKey;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        if ($this->_login === null) {
            $this->_login = Yii::$app->params['payme']['login'];
        }
        return $this->_login;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->getTestMode() ? $this->getTestSecretKey() : $this->getSecretKey();
    }

    public function getTestMode()
    {
        if ($this->_testMode === null) {
            $this->_testMode = Yii::$app->params['payme']['test_mode'];
        }
        return $this->_testMode;
    }

}
