<?php

namespace common\modules\payme\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\payme\models\PaymeTransaction;

class PaymeTransactionSearch extends PaymeTransaction
{

    public function rules()
    {
        return [
            [['id', 'code', 'state', 'amount', 'reason', 'time', 'cancel_time', 'create_time', 'perform_time'], 'integer'],
            [['transaction'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($query = null, $defaultPageSize = 20, $params = null)
    {

        if ($params === null) {
            $params = Yii::$app->request->queryParams;
        }
        if ($query == null) {
            $query = PaymeTransaction::find();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $defaultPageSize,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'code' => $this->code,
            'state' => $this->state,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'time' => $this->time,
            'cancel_time' => $this->cancel_time,
            'create_time' => $this->create_time,
            'perform_time' => $this->perform_time,
        ]);

        $query->andFilterWhere(['like', 'transaction', $this->transaction]);

        return $dataProvider;
    }
}
