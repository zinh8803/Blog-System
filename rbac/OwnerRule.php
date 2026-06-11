<?php

namespace app\rbac;

use yii\rbac\Rule;

class OwnerRule extends Rule
{
    public $name = 'isOwner';

    public function execute($user, $item, $params): bool
    {
        if (isset($params['post'])) {
            return (int) $params['post']->user_id === (int) $user;
        }

        if (isset($params['comment'])) {
            return (int) $params['comment']->user_id === (int) $user;
        }

        return false;
    }
}
