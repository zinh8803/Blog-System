<?php

namespace app\models\search;

use app\models\Post;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{
    public $page = 1;
    public $limit = 10;

    public function rules()
    {
        return [
            [['id', 'category_id', 'user_id', 'page', 'limit'], 'integer'],
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
        $query = Post::find()->notDeleted();
        return $this->buildDataProvider($query);
    }

    public function searchTrash($params)
    {
        $this->load($params, '');
        $query = Post::find()->deleted();
        return $this->buildDataProvider($query);
    }

    private function buildDataProvider($query)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' => max((int) $this->page - 1, 0),
                'pageSize' => (int) $this->limit ?: 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published_at' => $this->published_at,
        ]);
        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content]);
        return $dataProvider;
    }

}
