<?php

namespace app\models\search;

use app\models\PostDailyStat;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostDailyStatSearch extends PostDailyStat
{
    public $date;
    public $from;
    public $to;
    public $limit;

    public function rules()
    {
        return [
            [['date', 'from', 'to'], 'date', 'format' => 'php:Y-m-d'],
            [['limit'], 'integer', 'min' => 1],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $this->load($params, '');

        $query = PostDailyStat::find()->orderBy(['stat_date' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['stat_date' => $this->date])
            ->andFilterWhere(['>=', 'stat_date', $this->from])
            ->andFilterWhere(['<=', 'stat_date', $this->to]);

        return $dataProvider;
    }
}
