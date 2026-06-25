<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\tests\Support\FunctionalTester;

final class AuthCest
{
    public function loginReturnsAccessToken(FunctionalTester $I): void
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
        $I->seeResponseJsonMatchesJsonPath('$.data.access_token');
    }

    public function loginRejectsWrongPassword(FunctionalTester $I): void
    {
        $I->sendPost('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => 'wrong-password',
        ]);

        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 401,
            'status' => false,
            'message' => 'Invalid credentials',
        ]);
    }

    public function createCategoryRequiresAuthentication(FunctionalTester $I): void
    {
        $I->sendPost('/api/category', [
            'name' => 'auth-required-' . uniqid(),
            'status' => 1,
        ]);

        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 401,
            'status' => false,
        ]);
    }

    public function readerCannotCreateCategory(FunctionalTester $I): void
    {
        $I->sendPost('/api/auth/login', [
            'email' => 'reader@gmail.com',
            'password' => '123456',
        ]);

        $token = $I->grabDataFromResponseByJsonPath('$.data.access_token')[0];
        $I->haveHttpHeader('Authorization', "Bearer $token");

        $I->sendPost('/api/category', [
            'name' => 'reader-forbidden-' . uniqid(),
            'status' => 1,
        ]);

        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 403,
            'status' => false,
            'message' => 'You do not have permission to perform this action',
        ]);
    }

    public function authenticatedUserCanFetchProfile(FunctionalTester $I): void
    {
        $I->sendPost('/api/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '123456',
        ]);

        $token = $I->grabDataFromResponseByJsonPath('$.data.access_token')[0];

        $I->haveHttpHeader('Authorization', "Bearer $token");

        $I->sendGet('/api/auth/me');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'code' => 200,
            'status' => true,
            'message' => 'User profile',
        ]);
    }
}
