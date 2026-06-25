<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\Post;
use app\tests\Support\FunctionalTester;

final class PostCest
{
    private const CATEGORY_PREFIX = 'functional-post-category-';
    private const POST_PREFIX = 'functional-post-';

    public function _before(FunctionalTester $I): void
    {
        $I->sendPost('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '123456',
        ]);

        $token = $I->grabDataFromResponseByJsonPath('$.data.access_token')[0];
        $I->haveHttpHeader('Authorization', "Bearer $token");
    }

    public function _after(FunctionalTester $I): void
    {
        Post::deleteAll(['like', 'title', self::POST_PREFIX . '%', false]);
        Category::deleteAll(['like', 'name', self::CATEGORY_PREFIX . '%', false]);
    }

    public function getPostList(FunctionalTester $I): void
    {
        $I->sendGet('/api/post');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'Post list',
        ]);
    }

    public function createPostSuccessfully(FunctionalTester $I): void
    {
        $category = $this->createCategory();
        $title = self::POST_PREFIX . uniqid();

        $I->sendPost('/api/post', [
            'title' => $title,
            'summary' => 'Functional post summary',
            'content' => 'Functional post content is long enough.',
            'status' => Post::STATUS_DRAFT,
            'category_id' => $category->id,
            'tags' => [],
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 201,
            'status' => true,
            'message' => 'Post created successfully',
        ]);
        $I->seeRecord(Post::class, [
            'title' => $title,
            'category_id' => $category->id,
        ]);
    }

    public function createPostRejectsInvalidPayload(FunctionalTester $I): void
    {
        $I->sendPost('/api/post', [
            'title' => self::POST_PREFIX . uniqid(),
            'content' => 'short',
            'status' => 'archived',
            'category_id' => 999999,
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 422,
            'status' => false,
            'message' => 'Validation failed',
        ]);
    }

    public function updatePostSuccessfully(FunctionalTester $I): void
    {
        $category = $this->createCategory();
        $title = self::POST_PREFIX . uniqid();
        $this->createPostThroughApi($I, $category->id, $title);
        $postId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->sendPut('/api/post/' . $postId, [
            'title' => $title,
            'summary' => 'Updated summary',
            'content' => 'Updated functional post content is long enough.',
            'status' => Post::STATUS_PUBLISHED,
            'category_id' => $category->id,
            'tags' => [],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'Post updated successfully',
        ]);
        $I->seeRecord(Post::class, [
            'id' => $postId,
            'status' => Post::STATUS_PUBLISHED,
        ]);
    }

    public function deletePostSoftDeletesRecord(FunctionalTester $I): void
    {
        $category = $this->createCategory();
        $title = self::POST_PREFIX . uniqid();
        $this->createPostThroughApi($I, $category->id, $title);
        $postId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->sendDelete('/api/post/' . $postId);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'Post deleted successfully',
        ]);
        $I->seeRecord(Post::class, [
            'id' => $postId,
            'is_deleted' => 1,
        ]);
    }

    private function createCategory(): Category
    {
        $category = new Category();
        $category->name = self::CATEGORY_PREFIX . uniqid();
        $category->slug = $category->name;
        $category->status = 1;
        $category->save(false);

        return $category;
    }

    private function createPostThroughApi(FunctionalTester $I, int $categoryId, string $title): void
    {
        $I->sendPost('/api/post', [
            'title' => $title,
            'summary' => 'Functional post summary',
            'content' => 'Functional post content is long enough.',
            'status' => Post::STATUS_DRAFT,
            'category_id' => $categoryId,
            'tags' => [],
        ]);

        $I->seeResponseCodeIs(201);
    }
}
