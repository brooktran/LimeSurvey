<?php
namespace ls\models;

class SurveyTimingDynamic extends ActiveRecord
{
    protected static $sid = 0;

    /**
     * Returns the static model
     *
     * @static
     * @access public
     * @param int $surveyid
     * @return CActiveRecord
     */
    public static function model($sid = null)
    {
        $refresh = false;
        if (!is_null($sid)) {
            self::sid($sid);
            $refresh = true;
        }

        $model = parent::model(__CLASS__);

        //We need to refresh if we changed sid
        if ($refresh === true) {
            $model->refreshMetaData();
        }

        return $model;
    }

    /**
     * Sets the survey ID for the next model
     *
     * @static
     * @access public
     * @param int $sid
     * @return void
     */
    public static function sid($sid)
    {
        self::$sid = (int)$sid;
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * Defines the relations for this model
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        return array(
            'id' => array(self::BELONGS_TO, 'ls\models\SurveyDynamic', 'id'),
        );
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{survey_' . intval(self::$sid) . '_timings}}';
    }

    /**
     * Returns Time statistics for this answer table
     *
     * @access public
     * @return array
     */
    public function statistics()
    {
        $sid = self::$sid;
        if (Yii::app()->db->schema->getTable($this->tableName())) {
            $queryAvg = Yii::app()->db->createCommand()
                ->select("AVG(interviewtime) AS avg, COUNT(*) as count")
                ->from($this->tableName() . " t")
                ->join("{{survey_{$sid}}} s", "t.id = s.id")
                ->where("s.submitdate IS NOT NULL")
                ->queryRow();
            if ($queryAvg['count']) {
                $statistics['avgmin'] = (int)($queryAvg['avg'] / 60);
                $statistics['avgsec'] = $queryAvg['avg'] % 60;
                $statistics['count'] = $queryAvg['count'];
                $queryAll = Yii::app()->db->createCommand()
                    ->select("interviewtime")
                    ->from($this->tableName() . " t")
                    ->join("{{survey_{$sid}}} s", "t.id = s.id")
                    ->where("s.submitdate IS NOT NULL")
                    ->order("t.interviewtime")
                    ->queryAll();
                $middleval = intval($statistics['count'] / 2);
                $statistics['middleval'] = $middleval;
                if ($statistics['count'] % 2 && $statistics['count'] > 1) {
                    $median = ($queryAll[$middleval]['interviewtime'] + $queryAll[$middleval - 1]['interviewtime']) / 2;
                } else {
                    $median = $queryAll[$middleval]['interviewtime'];
                }
                $statistics['median'] = $median;
                $statistics['allmin'] = (int)($median / 60);
                $statistics['allsec'] = $median % 60;
            } else {
                $statistics['count'] = 0;
            }
        } else {
            $statistics['count'] = 0;
        }

        return $statistics;
    }

    public function insertRecords($data)
    {
        $record = new self;
        foreach ($data as $k => $v) {
            $record->$k = $v;
        }

        try {
            $record->save();

            return $record->id;
        } catch (Exception $e) {
            return false;
        }
    }
}

