<?php

namespace app\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class SendMailJob extends BaseObject implements JobInterface
{
    public string $to;
    public string $subject;
    public string $view;
    public array $params = [];

    public function execute($queue)
    {
        $from = getenv('MAIL_USERNAME') ?? 'noreply@example.com';

        Yii::$app->mailer
            ->compose($this->view, $this->params)
            ->setFrom([
                $from => 'Blog System',
            ])
            ->setTo($this->to)
            ->setSubject($this->subject)
            ->send();
    }
}
