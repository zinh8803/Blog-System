<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\models\Category;
use app\models\Post;
use app\tests\Support\AcceptanceTester;

final class LoginCest
{
    private const CATEGORY_PREFIX = 'acceptance-api-category-';
    private const POST_PREFIX = 'acceptance-api-post-';

    public function _after(AcceptanceTester $I): void
    {
        Post::deleteAll(['like', 'title', self::POST_PREFIX . '%', false]);
        Category::deleteAll(['like', 'name', self::CATEGORY_PREFIX . '%', false]);
    }

    public function apiUserCanCreateAndViewPost(AcceptanceTester $I): void
    {
        $I->sendPost('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '123456',
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'Login success',
        ]);

        $token = $I->grabDataFromResponseByJsonPath('$.data.access_token')[0];
        $I->haveHttpHeader('Authorization', "Bearer $token");

        $categoryName = self::CATEGORY_PREFIX . uniqid();
        $I->sendPost('/api/category', [
            'name' => $categoryName,
            'status' => 1,
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'code' => 201,
            'status' => true,
            'message' => 'Category created successfully',
        ]);
        $categoryId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $postTitle = self::POST_PREFIX . uniqid();
        $I->sendPost('/api/post', [
            'title' => $postTitle,
            'summary' => 'Acceptance API post summary',
            'content' => 'Acceptance API post content is long enough.',
            'status' => Post::STATUS_PUBLISHED,
            'category_id' => $categoryId,
            'tags' => [],
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'code' => 201,
            'status' => true,
            'message' => 'Post created successfully',
        ]);
        $postId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->sendGet('/api/post/' . $postId);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'Post retrieved successfully',
            'data' => [
                'title' => $postTitle,
                'category_id' => (string) $categoryId,
            ],
        ]);
    }
}
