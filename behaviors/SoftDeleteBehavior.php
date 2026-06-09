<?php
namespace app\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class SoftDeleteBehavior extends Behavior
{
    public string $attribute = 'deleted_at';
    public string $isDeletedAttribute = 'is_deleted';

    public function softDelete(): bool
    {
        $this->owner->{$this->attribute} = time();
        $this->owner->{$this->isDeletedAttribute} = true;

        return $this->owner->save(false, [$this->attribute, $this->isDeletedAttribute]);
    }

    public function restore(): bool
    {
        $this->owner->{$this->attribute} = null;
        $this->owner->{$this->isDeletedAttribute} = false;

        return $this->owner->save(false, [$this->attribute, $this->isDeletedAttribute]);
    }

    public function isDeleted(): bool
    {
        return $this->owner->{$this->isDeletedAttribute} === true;
    }
}
