<?php

namespace app\rbac;

use yii\rbac\Rule;

class PostOwnerRule extends Rule
{
    public $name = 'isPostOwner';

    public function execute($user, $item, $params): bool
    {
        return isset($params['post'])
            && (int) $params['post']->user_id === (int) $user;
    }
}
