<?php

namespace app\commands;

use app\models\Post;
use app\models\Comment;
use app\models\Like;
use app\models\PostDailyStat;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;

class PostStatsController extends Controller
{
    private const TABLE = 'post_daily_stats';
    private const MIN_VALID_CREATED_AT = 946684800; // 2000-01-01 00:00:00 UTC

    /**
     * Build daily post statistics.
     *
     * Usage:
     * - php yii post-stats/daily
     * - php yii post-stats/daily 2026-06-25
     */
    public function actionDaily(?string $date = null): int
    {
        if ($date !== null && !$this->isValidDate($date)) {
            $this->stderr("Invalid date: {$date}. Use Y-m-d format.\n");
            return ExitCode::DATAERR;
        }

        $stats = $this->buildStats($date);
        $this->saveStats($stats, $date);

        if (empty($stats)) {
            $this->stdout("No posts found.\n");
            return ExitCode::OK;
        }

        $this->stdout('Saved post stats: ' . count($stats) . " day(s).\n");

        return ExitCode::OK;
    }

    private function buildStats(?string $date = null): array
    {
        $likesQuery = Like::find()
            ->select([
                'post_id',
                'like_count' => 'COUNT(*)',
            ])
            ->groupBy('post_id');

        $commentsQuery = Comment::find()
            ->select([
                'post_id',
                'comment_count' => 'COUNT(*)',
            ])
            ->groupBy('post_id');

        $query = Post::find()
            ->alias('p')
            ->select([
                'stat_date' => new Expression('DATE(FROM_UNIXTIME(p.created_at))'),
                'total_posts' => new Expression('COUNT(p.id)'),
                'published_posts' => new Expression("SUM(CASE WHEN p.status = :published THEN 1 ELSE 0 END)", [
                    ':published' => Post::STATUS_PUBLISHED,
                ]),
                'draft_posts' => new Expression("SUM(CASE WHEN p.status = :draft THEN 1 ELSE 0 END)", [
                    ':draft' => Post::STATUS_DRAFT,
                ]),
                'deleted_posts' => new Expression('SUM(CASE WHEN p.is_deleted = 1 THEN 1 ELSE 0 END)'),
                'total_views' => new Expression('COALESCE(SUM(p.view_count), 0)'),
                'total_likes' => new Expression('COALESCE(SUM(l.like_count), 0)'),
                'total_comments' => new Expression('COALESCE(SUM(c.comment_count), 0)'),
            ])
            ->leftJoin(['l' => $likesQuery], 'l.post_id = p.id')
            ->leftJoin(['c' => $commentsQuery], 'c.post_id = p.id')
            ->where(['>=', 'p.created_at', self::MIN_VALID_CREATED_AT])
            ->groupBy(new Expression('DATE(FROM_UNIXTIME(p.created_at))'))
            ->orderBy(['stat_date' => SORT_ASC])
            ->asArray();

        if ($date !== null) {
            $query->andWhere([
                'between',
                'p.created_at',
                strtotime($date . ' 00:00:00'),
                strtotime($date . ' 23:59:59'),
            ]);
        }

        $now = time();
        return array_map(static function (array $row) use ($now): array {
            return [
                'stat_date' => $row['stat_date'],
                'total_posts' => (int) $row['total_posts'],
                'published_posts' => (int) $row['published_posts'],
                'draft_posts' => (int) $row['draft_posts'],
                'deleted_posts' => (int) $row['deleted_posts'],
                'total_views' => (int) $row['total_views'],
                'total_likes' => (int) $row['total_likes'],
                'total_comments' => (int) $row['total_comments'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $query->all());
    }

    private function saveStats(array $stats, ?string $date = null): void
    {
        $db = Yii::$app->db;
        $condition = $date === null ? '' : ['stat_date' => $date];
        PostDailyStat::deleteAll($condition);

        if (!empty($stats)) {
            $db->createCommand()->batchInsert(PostDailyStat::tableName(), [
                'stat_date',
                'total_posts',
                'published_posts',
                'draft_posts',
                'deleted_posts',
                'total_views',
                'total_likes',
                'total_comments',
                'created_at',
                'updated_at',
            ], $stats)->execute();
        }
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
