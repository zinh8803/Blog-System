<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class AiLogQuery extends ActiveQuery
{
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    public function byAction($action)
    {
        return $this->andWhere(['action' => $action]);
    }

    public function latest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }

    public function success()
    {
        return $this->andWhere(['status' => 1]);
    }
    public function failed()
    {
        return $this->andWhere(['status' => 0]);
    }
}
