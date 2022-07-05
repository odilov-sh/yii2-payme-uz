<?php
/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 07.05.2022, 10:16
 */

namespace common\modules\payme\behaviors;

use common\modules\payme\components\AbstractPayme;
use common\modules\payme\components\Payme;
use common\modules\payme\models\PaymeTransaction;
use yii\base\Behavior;

/**
 *
 * @property-read \common\modules\payme\behaviors\PaymeRequest $request
 */
class PaymeResponse extends Behavior
{

    /**
     * Системная (внутренняя ошибка).
     */
    const SYSTEM_ERROR = -32400;

    /**
     * Неверная сумма.
     */
    const WRONG_AMOUNT = -31001;

    /**
     * Ошибки связанные с неверным пользовательским вводом "account".
     * Например: введенный логин не найден
     */
    const USER_NOT_FOUND = -31050;

    /**
     * Передан неправильный JSON-RPC объект.
     */
    const JSON_RPC_ERROR = -32600;

    /**
     * Транзакция не найдена.
     */
    const TRANS_NOT_FOUND = -31003;

    /**
     * Запрашиваемый метод не найден.
     * Поле data содержит запрашиваемый метод.
     */
    const METHOD_NOT_FOUND = -32601;

    /**
     * Ошибка Парсинга JSON.
     * Запрос является не валидным JSON объектом
     */
    const JSON_PARSING_ERROR = -32700;

    /**
     * Невозможно выполнить данную операцию.
     */
    const CANT_PERFORM_TRANS = -31008;

    /**
     * Невозможно отменить транзакцию.
     * Товар или услуга предоставлена Потребителю в полном объеме.
     */
    const CANT_CANCEL_TRANSACTION = -31007;

    /**
     * Выполняется другая транзакция
     */
    const ANOTHER_TRANSACTION_IS_PERFORMING = -31051;

    /**
     * Error. Order is waiting...
     */
    const NO_AUTH = -32504;

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
     * Create transaction uchun success otvet
     *
     * @param \common\modules\payme\models\PaymeTransaction $transaction
     * @return array
     */
    public function successCreateTransaction(PaymeTransaction $transaction)
    {
        return $this->success([
            'create_time' => (int)$transaction->create_time,
            'transaction' => (string)$transaction->id,
            'state' => $transaction-> state,
        ]);
    }

    /**
     * Check perform transaction uchun success otvet
     *
     * @return array
     */
    public function successCheckPerformTransaction()
    {
        return $this->success([
            "allow" => true
        ]);
    }

    /**
     * Perform transaction uchun success otvet
     * Transaksiya qabul qilingandan keyin chaqiriladi
     *
     * @param PaymeTransaction $transaction
     * @return array
     */
    public function successPerformTransaction(PaymeTransaction $transaction)
    {
        return $this->success([
            "state" => $transaction->state,
            "perform_time" => (int)$transaction->perform_time,
            "transaction" => (string)$transaction->id,
        ]);
    }

    /**
     * Check transaction
     *
     * @param PaymeTransaction $transaction
     * @return array
     */
    public function successCheckTransaction(PaymeTransaction $transaction)
    {
        return $this->success([
            "create_time" => (int)$transaction->create_time,
            "perform_time" => (int)$transaction->perform_time,
            "cancel_time" => (int)$transaction->cancel_time,
            "transaction" => (string)$transaction->id,
            "state" => $transaction->state,
            "reason" => $transaction->reason
        ]);
    }

    /**
     * Transaksiya otmena bolganda shu method chaqiriladi
     *
     * @param $trans PaymeTransaction
     * @return array
     */
    public function successCancelTransaction(PaymeTransaction $trans)
    {
        return $this->success([
            "state" => $trans->state,
            "cancel_time" => (int)$trans->cancel_time,
            "transaction" => (string)$trans->id
        ]);
    }


    /**
     * Umumiy JSON-RPC uchun success method
     * Vazifasi id va boshqa parametrlarni olib quyadi requestdan
     *
     * @param array $result
     * @return array
     */
    public function success(array $result)
    {
        return [
            "error" => null,
            "result" => $result,
            "id" => $this->request->id
        ];
    }


    /**
     * Oshibka bulganda shu method ishga tushadi
     * Klientga oshibkani qaytarish uchun
     *
     * @param $code
     * @param array $message
     * @param null $data
     * @return array
     */
    public function error($code, $message = [], $data = null)
    {

        if (empty($message)) {
            $message = $this->getErrorMessage($code);
        }

        return [
            'error' => [
                "code" => $code,
                "message" => $message,
                "data" => $data
            ],
            'result' => null,
            'id' => $this->request->id
        ];
    }


    /**
     * Default opisaniyalar oshibkalar uchun
     *
     * @param $code
     * @return array|mixed
     */
    private function getErrorMessage($code)
    {
        $messages = [
            self::NO_AUTH => [
                "uz" => "Avtorizatsiyadan o'tilmadi",
                "ru" => "Нет авторизации",
                "en" => "No auth"
            ],
            self::SYSTEM_ERROR => [
                "uz" => "Ichki sestema hatoligi",
                "ru" => "Внутренняя ошибка сервера",
                "en" => "Internal server error"
            ],

            self::WRONG_AMOUNT => [
                "uz" => "Notug'ri summa.",
                "ru" => "Неверная сумма.",
                "en" => "Wrong amount.",
            ],

            self::USER_NOT_FOUND => [
                "uz" => "Foydalanuvchi topilmadi",
                "ru" => "Пользователь не найден",
                "en" => "User not found",
            ],

            self::JSON_RPC_ERROR => [
                "uz" => "Notog`ri JSON-RPC obyekt yuborilgan.",
                "ru" => "Передан неправильный JSON-RPC объект.",
                "en" => "Handed the wrong JSON-RPC object."
            ],

            self::TRANS_NOT_FOUND => [
                "uz" => "Transaction not found",
                "ru" => "Трансакция не найдена",
                "en" => "Transaksiya topilmadi"
            ],

            self::METHOD_NOT_FOUND => [
                "uz" => "Metod topilmadi",
                "ru" => "Запрашиваемый метод не найден.",
                "en" => "Method not found"
            ],

            self::JSON_PARSING_ERROR => [
                "uz" => "Json pars qilganda hatolik yuz berdi",
                "ru" => "Ошибка при парсинге JSON",
                "en" => "Error while parsing json"
            ],

            self::CANT_PERFORM_TRANS => [
                "uz" => "Bu operatsiyani bajarish mumkin emas",
                "ru" => "Невозможно выполнить данную операцию.",
                "en" => "Can't perform transaction",
            ],

            self::CANT_CANCEL_TRANSACTION => [
                "uz" => "Transaksiyani qayyarib bolmaydi",
                "ru" => "Невозможно отменить транзакцию",
                "en" => "You can not cancel the transaction"
            ],
            self::ANOTHER_TRANSACTION_IS_PERFORMING => [
                "uz" => "Boshqa transaksiya amalga oshirilmoqda",
                "ru" => "Выполняется другая транзакция",
                "en" => "Another transaction is performing"
            ]
        ];

        return $messages[$code] ?? [];
    }

    /**
     * @return array
     */
    public function noAuthError()
    {
        return $this->error(self::NO_AUTH);
    }

    /**
     * @return array
     */
    public function jsonRpcError()
    {
        return $this->error(self::JSON_RPC_ERROR);
    }

    /**
     * @return array
     */
    public function userNotFoundError()
    {
        return $this->error(self::USER_NOT_FOUND);
    }

    /**
     * @return array
     */
    public function wrongAmountError()
    {
        return $this->error(self::WRONG_AMOUNT);
    }

    /**
     * @return array
     */
    public function timeOutError()
    {
        return $this->error(self::CANT_PERFORM_TRANS, [
            "uz" => "Vaqt tugashi o'tdi",
            "ru" => "Тайм-аут прошел",
            "en" => "Timeout passed"
        ]);
    }

    /**
     * @return array
     */
    public function cannotPerformTransaction()
    {
        return $this->error(self::CANT_PERFORM_TRANS);
    }

    /**
     * @return array
     */
    public function transNotFoundError()
    {
        return $this->error(self::TRANS_NOT_FOUND);
    }

    /**
     * @return array
     */
    public function cannotCancelTransError()
    {
        return $this->error(self::CANT_CANCEL_TRANSACTION);
    }

    /**
     * @return array
     */
    public function anotherTransactionIsPerformingError()
    {
        return $this->error(self::ANOTHER_TRANSACTION_IS_PERFORMING);
    }
}
