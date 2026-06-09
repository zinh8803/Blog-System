<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class CategoryQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => 1]);
    }

   
}
