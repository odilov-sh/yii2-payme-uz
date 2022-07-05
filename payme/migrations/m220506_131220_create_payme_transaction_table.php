<?php

namespace common\modules\payme\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%payme_transaction}}`.
 */
class m220506_131220_create_payme_transaction_table extends Migration
{

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('payme_uz_transaction', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer(),
            'user_payment_id' => $this->integer(),
            'transaction' => $this->string(25)->notNull(),
            'code' => $this->integer(11),
            'state' => $this->integer(2)->notNull(),
            'amount' => $this->integer(11)->notNull(),
            'reason' => $this->integer(3),
            'time' => $this->bigInteger(15)->unsigned()->notNull()->defaultValue(0),
            'cancel_time' => $this->bigInteger(15)->unsigned()->notNull()->defaultValue(0),
            'create_time' => $this->bigInteger(15)->unsigned()->notNull()->defaultValue(0),
            'perform_time' => $this->bigInteger(15)->unsigned()->notNull()->defaultValue(0)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('payme_uz_transaction');
    }

}
