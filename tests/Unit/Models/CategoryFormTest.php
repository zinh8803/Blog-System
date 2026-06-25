<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;
use app\models\forms\category\CategoryForm;
use Codeception\Test\Unit;

final class CategoryFormTest extends Unit
{
    private const PREFIX = 'test-category-form-';

    protected function _after(): void
    {
        Category::deleteAll(['like', 'name', self::PREFIX . '%', false]);
    }

    public function testCreateCategoryPersistsSlugAndStatus(): void
    {
        $form = new CategoryForm(['scenario' => CategoryForm::SCENARIO_CREATE]);
        $form->name = self::PREFIX . 'news';
        $form->status = 1;

        $category = $form->createCategory();

        verify($category)->notNull();
        verify($category->name)->equals(self::PREFIX . 'news');
        verify($category->slug)->equals(self::PREFIX . 'news');
        verify((int) $category->status)->equals(1);
    }

    public function testCreateCategoryRejectsDuplicateName(): void
    {
        $existing = new Category();
        $existing->name = self::PREFIX . 'duplicate';
        $existing->slug = $existing->name;
        $existing->status = 1;
        verify($existing->save(false))->true();

        $form = new CategoryForm(['scenario' => CategoryForm::SCENARIO_CREATE]);
        $form->name = self::PREFIX . 'duplicate';
        $form->status = 1;

        verify($form->createCategory())->null();
        verify($form->hasErrors('name'))->true();
    }

    public function testUpdateCategoryAllowsKeepingCurrentName(): void
    {
        $category = new CategoryForm(['scenario' => CategoryForm::SCENARIO_CREATE]);
        $category->name = self::PREFIX . 'editable';
        $category->slug = $category->name;
        $category->status = 0;
        verify($category->save(false))->true();

        $category->scenario = CategoryForm::SCENARIO_UPDATE;
        $category->status = 1;

        verify($category->updateCategory())->notNull();
        verify($category->hasErrors('name'))->false();
        verify((int) $category->status)->equals(1);
    }
}
