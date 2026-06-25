<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;
use app\models\Comment;
use app\models\forms\comment\CommentForm;
use app\models\Post;
use app\models\User;
use Codeception\Test\Unit;
use Yii;

final class CommentFormTest extends Unit
{
    private const PREFIX = 'test-comment-form-';

    protected function _after(): void
    {
        Yii::$app->user->logout();
        Comment::deleteAll(['like', 'content', self::PREFIX . '%', false]);
        Post::deleteAll(['like', 'title', self::PREFIX . '%', false]);
        Category::deleteAll(['like', 'name', self::PREFIX . '%', false]);
        User::deleteAll(['like', 'username', self::PREFIX . '%', false]);
    }

    public function testCreateCommentRequiresExistingPostAndContentLength(): void
    {
        $form = new CommentForm(['scenario' => CommentForm::SCENARIO_CREATE]);
        $form->post_id = 999999;
        $form->content = 'x';

        verify($form->validate())->false();
        verify($form->hasErrors('post_id'))->true();
        verify($form->hasErrors('content'))->true();
    }

    public function testCreateCommentPersistsCurrentUserAndVisibleStatus(): void
    {
        $this->loginUser();
        $post = $this->createPost();

        $form = new CommentForm(['scenario' => CommentForm::SCENARIO_CREATE]);
        $form->post_id = $post->id;
        $form->content = self::PREFIX . 'parent comment';

        $comment = $form->createComment();

        verify($comment)->notNull();
        verify((int) $comment->user_id)->equals((int) Yii::$app->user->id);
        verify($comment->status)->equals('visible');
    }

    public function testCannotReplyToReplyComment(): void
    {
        $this->loginUser();
        $post = $this->createPost();
        $parent = $this->createComment($post->id, null, self::PREFIX . 'parent');
        $reply = $this->createComment($post->id, $parent->id, self::PREFIX . 'reply');

        $form = new CommentForm(['scenario' => CommentForm::SCENARIO_CREATE]);
        $form->post_id = $post->id;
        $form->parent_id = $reply->id;
        $form->content = self::PREFIX . 'nested reply';

        verify($form->validate())->false();
        verify($form->hasErrors('parent_id'))->true();
    }

    public function testUpdateStatusOnlyAcceptsVisibleOrHidden(): void
    {
        $form = new CommentForm(['scenario' => CommentForm::SCENARIO_UPDATE_STATUS]);
        $form->status = 'spam';

        verify($form->validate())->false();
        verify($form->hasErrors('status'))->true();

        $form->status = 'hidden';
        verify($form->validate())->true();
    }

    private function loginUser(): User
    {
        $user = new User();
        $user->username = self::PREFIX . uniqid('user-', true);
        $user->email = $user->username . '@example.test';
        $user->setPassword('secret');
        verify($user->save(false))->true();
        Yii::$app->user->login($user);

        return $user;
    }

    private function createPost(): Post
    {
        $category = new Category();
        $category->name = self::PREFIX . uniqid('category-', true);
        $category->slug = $category->name;
        $category->status = 1;
        verify($category->save(false))->true();

        $post = new Post();
        $post->category_id = $category->id;
        $post->title = self::PREFIX . uniqid('post-', true);
        $post->slug = $post->title;
        $post->content = 'A post with content long enough for comments.';
        $post->status = Post::STATUS_DRAFT;
        verify($post->save(false))->true();

        return $post;
    }

    private function createComment(int $postId, ?int $parentId, string $content): Comment
    {
        $comment = new Comment();
        $comment->post_id = $postId;
        $comment->parent_id = $parentId;
        $comment->content = $content;
        verify($comment->save(false))->true();

        return $comment;
    }
}
