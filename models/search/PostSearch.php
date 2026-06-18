<?php

namespace app\models\search;

use app\models\Post;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{
    public function rules()
    {
        return [
            [['id', 'category_id', 'user_id'], 'integer'],
            [['title', 'content', 'status', 'created_at', 'updated_at', 'published_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $this->load($params, '');

        $query = Post::find()
            ->alias('p')
            ->select([
                'p.*',
                'like_count' => 'COUNT(l.id)',
            ])
            ->leftJoin(['l' => 'likes'], 'l.post_id = p.id')
            ->notDeleted()
            ->with('thumbnailFile')
            ->groupBy('p.id')
            ->orderBy(['p.id' => SORT_DESC]);

        return $this->buildDataProvider($query);
    }

    public function searchTrash($params)
    {
        $this->load($params, '');

        $query = Post::find()
            ->alias('p')
            ->select([
                'p.*',
                'like_count' => 'COUNT(l.id)',
            ])
            ->leftJoin(['l' => 'likes'], 'l.post_id = p.id')
            ->deleted()
            ->with('thumbnailFile')
            ->groupBy('p.id')
            ->orderBy(['p.id' => SORT_DESC]);

        return $this->buildDataProvider($query);
    }

    private function buildDataProvider($query)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'p.id' => $this->id,
            'p.user_id' => $this->user_id,
            'p.category_id' => $this->category_id,
            'p.status' => $this->status,
            'p.created_at' => $this->created_at,
            'p.updated_at' => $this->updated_at,
            'p.published_at' => $this->published_at,
        ]);
        $query->andFilterWhere(['like', 'p.title', $this->title])
            ->andFilterWhere(['like', 'p.content', $this->content]);
        return $dataProvider;
    }

}
