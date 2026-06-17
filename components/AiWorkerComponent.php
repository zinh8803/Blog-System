<?php

namespace app\components;

use app\models\AiLog;
use yii\base\Component;

class AiWorkerComponent extends Component
{
    public string $accountId;
    public string $apiToken;
    public string $model;

    private function request(string $action, string $prompt): string
    {
        $start = microtime(true);

        $log = new AiLog();
        $log->action = $action;
        $log->prompt_size = mb_strlen($prompt);
        $log->response_size = 0;
        $log->status = 0;
        $log->duration_ms = 0;
        $log->error_message = '';

        try {
            $url = sprintf(
                'https://api.cloudflare.com/client/v4/accounts/%s/ai/run/%s',
                $this->accountId,
                $this->model
            );

            $payload = [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ];

            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiToken,
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 5,
            ]);

            $result = curl_exec($ch);

            if ($result === false) {
                $error = curl_error($ch);
                curl_close($ch);

                throw new \RuntimeException($error);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \RuntimeException('AI service returned HTTP ' . $httpCode);
            }

            $response = json_decode($result, true);

            $text = $response['result']['response'] ?? '';

            $log->response_size = mb_strlen($text);
            $log->status = 1;
            $log->duration_ms = (int) ((microtime(true) - $start) * 1000);

            $log->save(false);

            return $text;

        } catch (\Throwable $e) {

            $log->status = 0;
            $log->error_message = substr($e->getMessage(), 0, 255);
            $log->duration_ms = (int) ((microtime(true) - $start) * 1000);

            $log->save(false);

            throw $e;
        }
    }

    public function generateTitle(string $description): array
    {
        $prompt = <<<PROMPT
        Generate 5 engaging Vietnamese blog titles.

        Requirements:
        - Return exactly 5 titles.
        - Each title must be unique.
        - Return only titles, one title per line.

        Description:
        {$description}
        PROMPT;

        $text = $this->request('generate-title', $prompt);

        return array_values(array_filter(array_map(
            static fn($line) => trim(preg_replace('/^\d+[\.\)]\s*/', '', $line)),
            preg_split('/\r\n|\r|\n/', trim($text))
        )));
    }

    public function generateSummary(string $content): string
    {
        $prompt = <<<PROMPT
        Summarize the following article in Vietnamese.

        Requirements:
        - Write exactly 2 short paragraphs.
        - Total length must not exceed 120 Vietnamese words.
        - Focus only on the key ideas.
        - Do not repeat details.
        - Return only the summary.

        Article:
        {$content}
        PROMPT;
        return $this->request('generate-summary', $prompt);
    }

    public function rewrite(string $text, string $instruction): string
    {
        $prompt = <<<PROMPT
        Rewrite the following text.

        Instruction:
        {$instruction}

        Text:
        {$text}

        Return only the rewritten text.
        PROMPT;
        return $this->request('rewrite', $prompt);
    }
}
