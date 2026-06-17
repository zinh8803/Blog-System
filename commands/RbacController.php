<?php

namespace app\commands;

use app\models\User;
use app\rbac\OwnerRule;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        $permissions = [
            'category.index',
            'category.view',
            'category.update',
            'category.create',
            'category.delete',

            'post.index',
            'post.view',
            'post.create',
            'post.update',
            'post.delete',
            'post.updateOwn',
            'post.deleteOwn',
            'post.updateStatus',
            'post.restore',
            'post.restoreOwn',

            'tag.index',
            'tag.view',
            'tag.create',
            'tag.update',
            'tag.delete',

            'user.index',
            'user.view',
            'user.update',
            'user.delete',

            'comment.index',
            'comment.view',
            'comment.create',
            'comment.update',
            'comment.delete',
            'comment.updateOwn',
            'comment.deleteOwn',

            'like.toggle',

            'file.index',
            'file.view',
            'file.create',
            'file.update',
            'file.updateOwn',

            'aiLog.index',
            'aiLog.view',

            'ai.use',
        ];

        foreach ($permissions as $permissionName) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $permissionName;
            $auth->add($permission);
        }

        $rule = new OwnerRule();
        $auth->add($rule);

        $commentUpdateOwn = $auth->createPermission('comment.updateOwn');
        $commentUpdateOwn->ruleName = $rule->name;
        $auth->update('comment.updateOwn', $commentUpdateOwn);

        $commentDeleteOwn = $auth->createPermission('comment.deleteOwn');
        $commentDeleteOwn->ruleName = $rule->name;
        $auth->update('comment.deleteOwn', $commentDeleteOwn);

        $postUpdateOwn = $auth->getPermission('post.updateOwn');
        $postUpdateOwn->ruleName = $rule->name;
        $auth->update('post.updateOwn', $postUpdateOwn);

        $postDeleteOwn = $auth->getPermission('post.deleteOwn');
        $postDeleteOwn->ruleName = $rule->name;
        $auth->update('post.deleteOwn', $postDeleteOwn);

        $postRestoreOwn = $auth->getPermission('post.restoreOwn');
        $postRestoreOwn->ruleName = $rule->name;
        $auth->update('post.restoreOwn', $postRestoreOwn);

        $fileUpdateOwn = $auth->getPermission('file.updateOwn');
        $fileUpdateOwn->ruleName = $rule->name;
        $auth->update('file.updateOwn', $fileUpdateOwn);

        $auth->addChild($postUpdateOwn, $auth->getPermission('post.update'));
        $auth->addChild($postDeleteOwn, $auth->getPermission('post.delete'));
        $auth->addChild($postRestoreOwn, $auth->getPermission('post.restore'));
        $auth->addChild($commentUpdateOwn, $auth->getPermission('comment.update'));
        $auth->addChild($commentDeleteOwn, $auth->getPermission('comment.delete'));
        $auth->addChild($fileUpdateOwn, $auth->getPermission('file.update'));

        $reader = $auth->createRole('reader');
        $auth->add($reader);

        $author = $auth->createRole('author');
        $auth->add($author);

        $admin = $auth->createRole('admin');
        $auth->add($admin);

        foreach ([
                     'comment.create',
                     'comment.updateOwn',
                     'comment.deleteOwn',
                     'like.toggle',
                 ] as $permissionName) {
            $auth->addChild($reader, $auth->getPermission($permissionName));
        }

        foreach ([
                     'tag.create',
                     'tag.update',
                     'tag.delete',
                     'post.create',
                     'post.updateOwn',
                     'post.deleteOwn',
                     'post.restoreOwn',
                     'comment.create',
                     'comment.updateOwn',
                     'comment.deleteOwn',
                     'like.toggle',
                     'file.create',
                     'file.updateOwn',
                     'ai.use',
                 ] as $permissionName) {
            $permission = $auth->getPermission($permissionName);

            if ($permission) {
                $auth->addChild($author, $permission);
            }
        }

        $auth->addChild($author, $reader);

        foreach ($permissions as $permissionName) {
            $permission = $auth->getPermission($permissionName);

            if ($permission) {
                $auth->addChild($admin, $permission);
            }
        }


        $this->actionCreateUser('admin', 'admin@gmail.com', 'admin');
        $this->actionCreateUser('admin1', 'admin1@gmail.com', 'admin');
        $this->actionCreateUser('reader', 'reader@gmail.com', 'reader');
        $this->actionCreateUser('reader1', 'reader1@gmail.com', 'reader');
        $this->actionCreateUser('reader2', 'reader2@gmail.com', 'reader');
        $this->actionCreateUser('vinh1', 'vinh1@gmail.com', 'author');
        $this->actionCreateUser('vinh2', 'vinh2@gmail.com', 'author');
        $this->actionCreateUser('author1', 'author1@gmail.com', 'author');
        $this->actionCreateUser('author2', 'author2@gmail.com', 'author');
        echo "RBAC init success\n";
    }

    public function actionCreateUser($username, $email, $role)
    {
        $auth = Yii::$app->authManager;

        $rbacRole = $auth->getRole($role);
        if (!$rbacRole) {
            echo "Role {$role} does not exist.\n";
            return;
        }

        $user = User::findOne(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->setPassword('123456');
            $user->generateAccessToken();
            $user->generateAuthKey();

            if (!$user->save(false)) {
                echo "Create user failed.\n";
                return;
            }
        }

        $auth->revokeAll($user->id);
        $auth->assign($rbacRole, $user->id);

        echo "User {$email} assigned role {$role} successfully.\n";
    }
}
