<?php

namespace app\models\search;

use app\models\Comment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CommentSearch extends Comment
{

    public function rules()
    {
        return [
            [['id', 'post_id', 'user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $postId = null)
    {
        $this->load($params, '');
        $query = Comment::find()
            ->where([
                'post_id' => $postId,
                'parent_id' => null,
            ])
            ->with([
                'replies.user',
            ])->orderBy(['created_at' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'post_id' => $this->post_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        $query->andFilterWhere(['like', 'content', $this->content]);
        return $dataProvider;
    }
}
