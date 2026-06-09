<?php

namespace app\models\query;

class FileQuery extends \yii\db\ActiveQuery
{
    public function byUser($userId)
    {
        return $this->andWhere(['create_by' => $userId]);
    }
    public function latest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }
            
}
