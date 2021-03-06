<?php

namespace fredyns\suite\widgets;

use yii\helpers\ArrayHelper;
use fredyns\suite\libraries\ActionControl;

/**
 * Generate blamable profile & timestamp in DetailView
 *
 * @author Fredy Nurman Saleh <email@fredyns.net>
 */
class BlamingDetail extends \yii\base\Widget
{
    public $model;
    public $blamed;
    public $timestamp;
    public $timeZone   = 'Asia/Jakarta';
    public $dateformat = 'php:d M Y, H:i (e, P)';
    public $actionControl;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty($this->model))
        {
            return '';
        }

        $data   = [];
        $blamed = $this->parseBlamed();
        $time   = $this->parseTimestamp();

        if ($blamed)
        {
            $data[] = 'by '.$blamed;
        }

        if ($time)
        {
            $data[] = 'at '.$time;
        }

        return implode(' ', $data);
    }

    /**
     * getting formated time information
     *
     * @return string
     */
    public function parseTimestamp()
    {
        if (empty($this->timestamp))
        {
            return;
        }

        $value = ArrayHelper::getValue($this->model, $this->timestamp);

        if (empty($this->timestamp))
        {
            return;
        }

        $formatter              = clone \Yii::$app->formatter;
        $formatter->timeZone    = $this->timeZone;
        $formatter->nullDisplay = null;

        return $formatter->asDate($value, $this->dateformat);
    }

    /**
     * getting blamed profile information (name)
     * create link to view profile if available
     *
     * @return string
     */
    public function parseBlamed()
    {
        if (empty($this->blamed))
        {
            return;
        }

        $model = ArrayHelper::getValue($this->model, $this->blamed);

        if (empty($model))
        {
            return;
        }

        $name = ArrayHelper::getValue($model, 'name');

        if (empty($name))
        {
            return;
        }

        if (empty($this->actionControl))
        {
            return $name;
        }

        try
        {
            $actionControl = \Yii::createObject([
                    'class' => $this->actionControl,
                    'model' => $model,
            ]);
        }
        catch (\Exception $ex)
        {
            return $name;
        }

        if ($actionControl instanceof ActionControl)
        {
            return $actionControl->getLinkTo([
                    'label'       => $name,
                    'linkOptions' => [
                        'title'  => 'view profile',
                        'target' => '_blank',
                    ],
            ]);
        }

        return $name;
    }

}