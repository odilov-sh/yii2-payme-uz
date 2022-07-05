<?php

/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 07.05.2022, 8:50
 */

namespace common\modules\payme\components;

use common\modules\payme\behaviors\PaymeResponse;
use common\modules\payme\models\PaymeTransaction;
use common\services\TelegramService;

/**
 *
 * Payme component to handle all Payme merchant methods.
 * @see https://developer.help.paycom.uz/uz/metody-merchant-api
 *
 */
class Payme extends AbstractPayme
{
    /**
     * List fields
     *
     * @var array $accounts
     */
    public $accounts = ["id"];

    //<editor-fold desc="Check Perform Transaction" defaultstate="collapsed">

    /**
     * Transaksiya otkazib bolish imkoniyatini tekshiradi
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function checkPerformTransaction()
    {
        if (!$this->access->hasAuth()) {
            return $this->response->noAuthError();
        }

        // Check account fields
        if (!$this->request->hasAccounts($this->accounts) || !$this->request->hasParam(["amount"])) {
            return $this->response->jsonRpcError();
        }

        // Check user
        if (!$this->access->hasUser()) {
            return $this->response->userNotFoundError();
        }

        // Check amount
        if ($this->access->isWrongAmount()) {
            return $this->response->wrongAmountError();
        }

        // Success
        return $this->response->successCheckPerformTransaction();
    }


    //</editor-fold>

    //<editor-fold desc="Create Transaction" defaultstate="collapsed">

    /**
     * Transaksiya yaratadi
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function createTransaction()
    {

        // Check auth
        if (!$this->access->hasAuth()) {
            return $this->response->noAuthError();
        }

        // Check account fields
        if (!$this->request->hasAccounts($this->accounts) || !$this->request->hasParam(["amount", "time", "id"])) {
            return $this->response->jsonRpcError();
        }

        // Check amount
        if ($this->access->isWrongAmount()) {
            return $this->response->wrongAmountError();
        }

        // Check user
        if (!$this->access->hasUser()) {
            return $this->response->userNotFoundError();
        }


        $transId = $this->request->getTransId();
        $time = $this->request->getParam("time");
        $userId = $this->request->getUserId();

        if ($this->access->isPerformingAnotherTransaction($userId, $transId)) {
            return $this->response->anotherTransactionIsPerformingError();
        }

        // if transaction exists on db
        if ($trans = $this->provider->getByTransId($transId)) {

            if (!$trans->getIsWaiting()) {
                return $this->response->error(PaymeResponse::CANT_PERFORM_TRANS);
            }

            return $this->response->successCreateTransaction($trans);
        }

        // Add new transaction
        try {
            $trans = $this->provider->insertTransaction([
                'transaction' => $transId,
                'time' => $time,
                'amount' => $this->request->getAmount(),
                'state' => PaymeTransaction::STATE_WAITING,
                'create_time' => $this->microtime(),
                'user_id' => $userId,
            ]);

            if ($trans) {
                return $this->response->successCreateTransaction($trans);

            }

        } catch (\Exception $e) {
            TelegramService::log("Payme insert transaction error\n" . $e->getMessage());
        }

        return $this->response->error(PaymeResponse::SYSTEM_ERROR);

    }

    //</editor-fold>

    //<editor-fold desc="Perform Transaction" defaultstate="collapsed">

    /**
     * Transaksiyani utqazish va foydalanuvchi hisobiga pul otqazish
     *
     * @return array
     */
    public function performTransaction()
    {
        // Check auth
        if (!$this->access->hasAuth()) {
            return $this->response->noAuthError();
        }

        // Check fields
        if (!$this->request->hasParam(["id"])) {
            return $this->response->jsonRpcError();
        }

        // Search by id
        $transId = $this->request->getTransId();
        $trans = $this->provider->getByTransId($transId);

        if (!$trans) {
            return $this->response->transNotFoundError();
        }

        // if transaction already performed, return success response
        if ($trans->getIsPerformed()) {
            return $this->response->successPerformTransaction($trans);
        }

        // Check timeout
        if (!$this->access->checkTimeout($trans->create_time)) {
            $trans->updateTransaction([
                "state" => PaymeTransaction::STATE_CANCELED_AFTER_PERFORMED,
                "reason" => 4
            ]);

            return $this->response->timeOutError();
        }

        try {

            // Perform transaction
            if ($trans->performTransaction()) {
                return $this->response->successPerformTransaction($trans);
            }

        } catch (\Exception $e) {
            TelegramService::log("Payme perform transaction error\n" . $e->getMessage());
        }

        return $this->response->cannotPerformTransaction();
    }


    //</editor-fold>

    //<editor-fold desc="Check Transaction" defaultstate="collapsed">

    /**
     * Transaksiyani statusini tekshiradi
     *
     * @return array
     */
    public function checkTransaction()
    {

        // Check auth
        if (!$this->access->hasAuth()) {
            return $this->response->noAuthError();
        }

        // Check fields
        if (!$this->request->hasParam(["id"])) {
            return $this->response->jsonRpcError();
        }

        $transId = $this->request->getTransId();
        $trans = $this->provider->getByTransId($transId);

        if ($trans) {
            return $this->response->successCheckTransaction($trans);
        } else {
            return $this->response->transNotFoundError();
        }


    }

    //</editor-fold>

    //<editor-fold desc="Cancel Transaction" defaultstate="collapsed">

    /**
     * Transaksiyani qaytarish va foydalanuvchi hisobidan yechib olish
     *
     * @return array
     */
    public function cancelTransaction()
    {

        //check auth
        if (!$this->access->hasAuth()) {
            return $this->response->noAuthError();
        }

        // Check fields
        if (!$this->request->hasParam(["id", "reason"])) {
            return $this->response->jsonRpcError();
        }

        $transId = $this->request->getTransId();
        $reason = $this->request->getParam("reason");

        $trans = $this->provider->getByTransId($transId);

        if (!$trans) {
            $this->response->transNotFoundError();
        }

        try {
            if ($trans->cancelTransaction($reason)) {
                return $this->response->successCancelTransaction($trans);
            }

        } catch (\Exception $e) {
            TelegramService::log("Payme cancel transaction error\n" . $e->getMessage());
        }

        return $this->response->cannotCancelTransError();

    }

    //</editor-fold>

    /**
     * Hozircha bu metod hech narsa qilmaydi, lekin keyin albatta qilaman
     */
    public function getStatement()
    {
        if (!$this->access->hasAuth()) {
            return $this->response->noAuthError();
        }
        // TODO: Implement GetStatement() method.
    }

    /**
     * Bu metod parolni uzgartirish uchun kk
     */
    public function changePassword()
    {
        if (!$this->access->hasAuth()) {
            return $this->response->noAuthError();
        }
        // TODO: Implement ChangePassword() method.
    }

}
