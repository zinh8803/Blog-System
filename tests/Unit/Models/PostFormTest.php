<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;
use app\models\forms\post\PostForm;
use app\models\Post;
use app\models\User;
use Codeception\Test\Unit;
use Yii;

final class PostFormTest extends Unit
{
    private const PREFIX = 'test-post-form-';

    protected function _after(): void
    {
        Yii::$app->user->logout();
        Post::deleteAll(['like', 'title', self::PREFIX . '%', false]);
        Category::deleteAll(['like', 'name', self::PREFIX . '%', false]);
        User::deleteAll(['like', 'username', self::PREFIX . '%', false]);
    }

    public function testLoadTracksWhetherTagsWereSubmitted(): void
    {
        $form = new PostForm(['scenario' => PostForm::SCENARIO_CREATE]);

        verify($form->load(['title' => self::PREFIX . 'without-tags'], ''))->true();
        verify($form->hasTagsInput)->false();

        verify($form->load(['title' => self::PREFIX . 'with-tags', 'tags' => ['yii', 'api']], ''))->true();
        verify($form->hasTagsInput)->true();
    }

    public function testCreateScenarioValidatesContentStatusAndCategory(): void
    {
        $form = new PostForm(['scenario' => PostForm::SCENARIO_CREATE]);
        $form->title = self::PREFIX . 'invalid';
        $form->content = 'too short';
        $form->status = 'archived';
        $form->category_id = 999999;

        verify($form->validate())->false();
        verify($form->hasErrors('content'))->true();
        verify($form->hasErrors('status'))->true();
        verify($form->hasErrors('category_id'))->true();
    }

    public function testCreateScenarioRejectsDuplicateTitle(): void
    {
        $this->loginUser();
        $category = $this->createCategory();
        $this->createPost(self::PREFIX . 'duplicate-title', $category->id);

        $form = new PostForm(['scenario' => PostForm::SCENARIO_CREATE]);
        $form->title = self::PREFIX . 'duplicate-title';
        $form->content = 'This is long enough content for validation.';
        $form->status = Post::STATUS_DRAFT;
        $form->category_id = $category->id;

        verify($form->validate())->false();
        verify($form->hasErrors('title'))->true();
    }

    public function testUpdateScenarioAllowsKeepingCurrentTitle(): void
    {
        $this->loginUser();
        $category = $this->createCategory();
        $post = $this->createPost(self::PREFIX . 'same-title', $category->id);

        $form = PostForm::findOne($post->id);
        $form->scenario = PostForm::SCENARIO_UPDATE;
        $form->content = 'Updated content remains long enough.';

        verify($form->validate())->true();
        verify($form->hasErrors('title'))->false();
    }

    public function testPublishedPostGetsPublishedTimestampOnSave(): void
    {
        $this->loginUser();
        $category = $this->createCategory();

        $post = new Post();
        $post->category_id = $category->id;
        $post->title = self::PREFIX . 'published';
        $post->slug = $post->title;
        $post->content = 'This post is ready to be published.';
        $post->status = Post::STATUS_PUBLISHED;

        verify($post->save(false))->true();
        verify($post->published_at)->notEmpty();
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

    private function createCategory(): Category
    {
        $category = new Category();
        $category->name = self::PREFIX . uniqid('category-', true);
        $category->slug = $category->name;
        $category->status = 1;
        verify($category->save(false))->true();

        return $category;
    }

    private function createPost(string $title, int $categoryId): Post
    {
        $post = new Post();
        $post->category_id = $categoryId;
        $post->title = $title;
        $post->slug = $title;
        $post->content = 'Existing content is long enough.';
        $post->status = Post::STATUS_DRAFT;
        verify($post->save(false))->true();

        return $post;
    }
}
