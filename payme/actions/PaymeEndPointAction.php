<?php
/*
 *  @author Shukurullo Odilov <shukurullo0321@gmail.com>
 *  @link telegram: https://t.me/yii2_dasturchi
 *  @date 09.05.2022, 8:59
 */

namespace common\modules\payme\actions;

use common\modules\payme\components\Payme;
use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * PaymeEndPointAction
 */
class PaymeEndPointAction extends Action
{

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return (new Payme(file_get_contents("php://input")))->response();
    }

}
