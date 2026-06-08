<?php
namespace app\behaviors;

use yii\behaviors\TimestampBehavior;

class Timestamp extends TimestampBehavior
{
    public $createdAtAttribute = 'created_at';
    public $updatedAtAttribute = 'updated_at';

    public $value;

    public function init()
    {
        parent::init();

        $this->value = time();
    }
}
