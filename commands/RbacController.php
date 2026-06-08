<?php

namespace app\commands;

use app\models\User;
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
            'post.updateStatus',

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

            'like.index',
            'like.view',
            'like.create',
            'like.update',
            'like.delete',

            'file.index',
            'file.view',
            'file.create',
            'file.update',
        ];

        foreach ($permissions as $permissionName) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $permissionName;
            $auth->add($permission);
        }

        $user = $auth->createRole('reader');
        $auth->add($user);

        $editor = $auth->createRole('author');
        $auth->add($editor);

        $admin = $auth->createRole('admin');
        $auth->add($admin);

        // permissions for reader role
        foreach ([
                     'post.index',
                     'post.view',
                     'tag.index',
                     'tag.view',

                     'category.index',
                     'comment.index',
                     'comment.view',
                     'comment.create',
                     'comment.update',
                     'comment.delete',

                     'like.index',
                     'like.view',
                     'like.create',
                     'like.update',
                     'like.delete',

                 ] as $permissionName) {
            $auth->addChild($user, $auth->getPermission($permissionName));
        }

        // permissions for author role
        foreach ([
                     'post.index',
                     'post.view',
                     'post.create',
                     'post.update',
                     'comment.index',
                     'comment.view',
                     'comment.create',
                     'comment.update',
                     'comment.delete',
                     'like.index',
                     'like.view',
                     'like.create',
                     'like.update',
                     'like.delete',
                 ] as $permissionName) {
            $auth->addChild($editor, $auth->getPermission($permissionName));
        }

        // permissions for admin role
        foreach ($permissions as $permissionName) {
            $auth->addChild($admin, $auth->getPermission($permissionName));
        }

        echo "RBAC init success\n";
    }

    public function actionCreateAdmin()
    {
        $user = User::findOne(['email' => 'admin@gmail.com']);

        if (!$user) {
            $user = new User();
            $user->username = 'admin';
            $user->email = 'admin@gmail.com';
            $user->setPassword('123456');
            $user->generateAccessToken();
            $user->generateAuthKey();
            // $user->created_at = date('Y-m-d H:i:s');
            // $user->updated_at = date('Y-m-d H:i:s');
            $user->save(false);
        }

        Yii::$app->authManager->assign(
            Yii::$app->authManager->getRole('admin'),
            $user->id
        );

        echo "Admin created successfully.\n";
    }

    public function actionCreateReader()
    {
        $user = User::findOne(['email' => 'reader@gmail.com']);
        if (!$user) {
            $user = new User();
            $user->username = 'reader';
            $user->email = 'reader@gmail.com';
            $user->setPassword('123456');
            $user->generateAccessToken();
            $user->generateAuthKey();
            $user->save(false);
        }

        Yii::$app->authManager->assign(
            Yii::$app->authManager->getRole('reader'),
            $user->id
        );

        echo "Reader created successfully.\n";
    }

    public function actionCreateAuthor()
    {
        $user = User::findOne(['email' => 'author@gmail.com']);
        if (!$user) {
            $user = new User();
            $user->username = 'author';
            $user->email = 'author@gmail.com';
            $user->setPassword('123456');
            $user->generateAccessToken();
            $user->generateAuthKey();
            $user->save(false);
        }

        Yii::$app->authManager->assign(
            Yii::$app->authManager->getRole('author'),
            $user->id
        );

        echo "Author created successfully.\n";
    }
}
