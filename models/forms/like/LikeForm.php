<?php

namespace app\models\forms\like;

use app\models\Like;

class LikeForm extends Like
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [

        ]);
    }
}
