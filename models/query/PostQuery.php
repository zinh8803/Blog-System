<?php

namespace app\models\query;
class PostQuery extends \yii\db\ActiveQuery
{
    public function published()
    {
        return $this->andWhere(['status' => 'published']);
    }
    public function draft()
    {
        return $this->andWhere(['status' => 'draft']);
    }
    public function deleted()
    {
        return $this->andWhere(['is_deleted' => true]);
    }

    public function notDeleted()
    {
        return $this->andWhere(['is_deleted' => false]);
    }

    public function withDeleted()
    {
        return $this;
    }
    public function byAuthor($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    public function byCategory($categoryId)
    {
        return $this->andWhere(['category_id' => $categoryId]);
    }

    public function latest()
    {
        return $this->orderBy(['created_at' => SORT_DESC]);
    }
}
