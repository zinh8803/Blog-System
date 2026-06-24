<?php

namespace app\models\forms\post;

use app\models\Category;
use app\models\File;
use app\models\Post;
use Yii;
use yii\web\UploadedFile;

class PostForm extends Post
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';
    public $tags = [];
    public bool $hasTagsInput = false;

    /** @var UploadedFile|null */
    public $imageFile;

    public function load($data, $formName = null)
    {
        $scope = $formName === '' ? $data : ($data[$formName ?? $this->formName()] ?? []);
        $this->hasTagsInput = is_array($scope) && array_key_exists('tags', $scope);

        return parent::load($data, $formName);
    }

    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['title', 'summary', 'content', 'status', 'category_id', 'imageFile', 'tags'],
            self::SCENARIO_UPDATE => ['title', 'summary', 'content', 'status', 'category_id', 'imageFile', 'tags'],
        ];
    }

    public function beforeValidate(): bool
    {
        if (!$this->imageFile instanceof UploadedFile) {
            $this->imageFile = UploadedFile::getInstanceByName('imageFile');
        }

        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            ['category_id', 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            ['thumbnail_file_id', 'exist', 'targetClass' => File::class, 'targetAttribute' => ['thumbnail_file_id' => 'id']],
            [['content'], 'string', 'min' => 10, 'max' => 10000],
            ['status', 'in', 'range' => [Post::STATUS_DRAFT, Post::STATUS_PUBLISHED]],
            ['tags', 'default', 'value' => []],
            [['title'], 'unique', 'targetClass' => Post::class, 'on' => self::SCENARIO_CREATE, 'message' => Yii::t('app', 'Title already exists.'),],
            [['title'], 'unique', 'targetClass' => Post::class, 'filter' => ['!=', 'id', $this->id],
                'on' => self::SCENARIO_UPDATE, 'message' => Yii::t('app', 'Title already exists.')],
            ['tags', 'each', 'rule' => ['string', 'max' => 50]],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['png', 'jpeg', 'webp'], 'checkExtensionByMimeType' => false, 'maxSize' => 5 * 1024 * 1024,],
        ]);
    }
}
