<?php

namespace app\models\search;

use app\models\File;
use yii\data\ActiveDataProvider;

class FileSearch extends File
{
    public function rules()
    {
        return [
            [['id', 'size', 'mime_type', 'created_by', 'created_at', 'updated_at'], 'integer'],
            [['original_name', 'storage', 'path', 'mime_type'], 'safe'],
        ];
    }

    public function search($params)
    {
        $this->load($params, '');
        $query = File::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);


        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        $query->andFilterWhere(['like', 'original_name', $this->original_name])
            ->andFilterWhere(['like', 'storage', $this->storage])
            ->andFilterWhere(['like', 'path', $this->path]);
        return $dataProvider;
    }
}
