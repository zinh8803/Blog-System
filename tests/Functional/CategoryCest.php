<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\tests\Support\FunctionalTester;

final class CategoryCest
{
    private const PREFIX = 'functional-category-';

    private string $token;

    public function _before(FunctionalTester $I): void
    {
        $I->sendPost('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '123456',
        ]);

        $this->token = $I->grabDataFromResponseByJsonPath('$.data.access_token')[0];

        $I->haveHttpHeader('Authorization', "Bearer {$this->token}");
    }

    public function _after(FunctionalTester $I): void
    {
        Category::deleteAll(['like', 'name', self::PREFIX . '%', false]);
    }

    public function getCategoryList(FunctionalTester $I)
    {
        $I->sendGet('/api/category');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'Category list',
        ]);
    }

    public function getCategory(FunctionalTester $I)
    {
        $category = $this->createCategory();

        $I->sendGet('/api/category/' . $category->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'Category view',
            'data' => [
                'name' => $category->name,
            ],
        ]);
    }

    public function getCategoryNotFound(FunctionalTester $I)
    {
        $I->sendGet('/api/category/999');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 404,
            'status' => false,
            'message' => 'Category not found',
        ]);
    }

    public function addCategory(FunctionalTester $I): void
    {
        $categoryName = self::PREFIX . uniqid();

        $I->sendPost('/api/category', [
            'name' => $categoryName,
            'status' => 1,
        ]);
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 201,
            'status' => true,
            'message' => 'Category created successfully',
        ]);
        $I->seeRecord(Category::class, [
            'name' => $categoryName,
            'status' => 1,
        ]);
    }

    public function addCategoryRejectsDuplicateName(FunctionalTester $I): void
    {
        $category = $this->createCategory();

        $I->sendPost('/api/category', [
            'name' => $category->name,
            'status' => 1,
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 422,
            'status' => false,
            'message' => 'Validation failed',
        ]);
    }

    private function createCategory(): Category
    {
        $category = new Category();
        $category->name = self::PREFIX . uniqid();
        $category->slug = $category->name;
        $category->status = 1;
        $category->save(false);

        return $category;
    }
}
