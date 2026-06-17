<?php

namespace app\models\forms\ai;

use yii\base\Model;

class AiForm extends Model
{
    public const SCENARIO_GENERATE_TITLE = 'generate_title';
    public const SCENARIO_GENERATE_SUMMARY = 'generate_summary';
    public const SCENARIO_REWRITE = 'rewrite';

    public ?string $description = null;
    public ?string $content = null;
    public ?string $text = null;
    public ?string $instruction = null;

    public function scenarios(): array
    {
        return [
            self::SCENARIO_GENERATE_TITLE => ['description'],
            self::SCENARIO_GENERATE_SUMMARY => ['content'],
            self::SCENARIO_REWRITE => ['text', 'instruction'],
        ];
    }

    public function rules(): array
    {
        return [
            [['description'], 'required', 'on' => self::SCENARIO_GENERATE_TITLE],
            [['content'], 'required', 'on' => self::SCENARIO_GENERATE_SUMMARY],
            [['text', 'instruction'], 'required', 'on' => self::SCENARIO_REWRITE],
            [['description'], 'string', 'max' => 500],
            [['content'], 'string', 'max' => 5000],
            [['text'], 'string', 'max' => 1000],
            [['instruction'], 'string', 'max' => 500],
        ];
    }
}
