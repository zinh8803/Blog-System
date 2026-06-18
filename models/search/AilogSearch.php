<?php

namespace app\models\search;

use app\models\AiLog;
use yii\data\ActiveDataProvider;

class AiLogSearch extends AiLog
{
    public function rules()
    {
        return [
            [['id', 'user_id', 'prompt_size', 'response_size', 'duration_ms', 'status', 'created_at', 'updated_at'], 'integer'],
            [['action'], 'safe'],
        ];
    }

    public function search($params)
    {
        $this->load($params, '');

        $query = AiLog::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'prompt_size' => $this->prompt_size,
            'response_size' => $this->response_size,
            'duration_ms' => $this->duration_ms,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        $query->andFilterWhere(['like', 'action', $this->action]);
        return $dataProvider;
    }
}
