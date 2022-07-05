<?php

namespace common\modules\payme\components;

use common\modules\payme\behaviors\PaymeAccess;
use common\modules\payme\behaviors\PaymeData;
use common\modules\payme\behaviors\PaymeRequest;
use common\modules\payme\behaviors\PaymeResponse;
use common\modules\payme\behaviors\TransactionProvider;
use common\modules\payme\models\PaymeTransaction;
use yii\base\Component;

/**
 *
 * @property-read PaymeRequest $request
 * @property-read PaymeResponse $response
 * @property-read TransactionProvider $provider
 * @property-read PaymeData $data
 * @property-read PaymeAccess $access
 * @property-read mixed $statement
 */
abstract class AbstractPayme extends Component
{

    public $requestData;

    /**
     * @var array $accounts
     */
    public $accounts = [];

    /**
     * Transaction timeout
     *
     * @var int $timeout
     */
    public $timeout = 600 * 1000;

    /**
     * @var array $error
     */
    public $error;

    /**
     * AbstractPayme constructor.
     * @param mixed $requestData
     */
    public function __construct($requestData, $config = [])
    {
        $this->requestData = $requestData;
        parent::__construct($config);
    }

    public function behaviors()
    {
        return [
            'request' => [
                'class' => PaymeRequest::class,
                'data' => $this->requestData,
            ],
            'response' => [
                'class' => PaymeResponse::class,
            ],
            'data' => [
                'class' => PaymeData::class,
            ],
            'access' => [
                'class' => PaymeAccess::class,
            ],
            'provider' => [
                'class' => TransactionProvider::class,
            ],
        ];
    }

    /**
     * @return PaymeRequest
     */
    public function getRequest()
    {
        return $this->getBehavior('request');
    }

    /**
     * @return PaymeResponse
     */
    public function getResponse()
    {
        return $this->getBehavior('response');
    }

    /**
     * @return PaymeData
     */
    public function getData()
    {
        return $this->getBehavior('data');
    }

    /**
     * @return TransactionProvider
     */
    public function getProvider()
    {
        return $this->getBehavior('provider');
    }

    /**
     * @return PaymeAccess
     */
    public function getAccess()
    {
        return $this->getBehavior('access');
    }

    /**
     * Transaksiya otkazib bolish imkoniyatini tekshiradi
     *
     * @return array
     */
    abstract public function checkPerformTransaction();


    /**
     * Transaksiya yaratadi
     *
     * @return array
     */
    abstract public function createTransaction();


    /**
     * Transaksiyani utqazish va foydalanuvchi hisobiga pul otqazish
     *
     * @return array
     */
    abstract public function performTransaction();


    /**
     * Transaksiyani qaytarish va foydalanuvchi hisobidan yechib olish
     *
     * @return array
     */
    abstract public function cancelTransaction();


    /**
     * Transaksiyani statusini tekshiradi
     *
     * @return array
     */
    abstract public function checkTransaction();


    /**
     * Hozircha bu metod hech narsa qilmaydi, lekin keyin albatta qilaman
     */
    abstract public function getStatement();


    /**
     * Bu metod parolni uzgartirish uchun kk
     */
    abstract public function changePassword();


    /**
     * Mikrotaym olish uchun
     *
     * @return int
     */
    public function microtime()
    {
        return (time() * 1000);
    }


    /**
     * Methodlarni chaqirib kerakli javobni serverga qaytaradi
     *
     * @return array
     */
    public function response()
    {
        $this->validate();

        if ($this->error) {
            return $this->response->error($this->error, $this->request->errorMessage, $this->request->errorData);
        } else {
            return $this->{$this->request->method}();
        }

    }


    /**
     * Validatsioya uchun metod
     */
    private function validate()
    {
        if (!$this->request->isValid()) {
            $this->error = $this->request->error;
        } elseif (!method_exists($this, $this->request->method)) {
            $this->error = PaymeResponse::METHOD_NOT_FOUND;
        }
    }


}
