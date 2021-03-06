<?php

namespace fredyns\suite\widgets;

use yii\helpers\ArrayHelper;
use fredyns\suite\libraries\ActionControl;

/**
 * Generate link in DetailView regarding it's Action control
 *
 * @author Fredy Nurman Saleh <email@fredyns.net>
 */
class LinkedDetail extends \yii\base\Widget
{
    public $model;
    public $attribute;
    public $actionControl;
    public $emptyMessage = '<span class="label label-warning">?</span>';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty($this->model))
        {
            return '';
        }

        $attr  = explode('.', $this->attribute);
        $model = ArrayHelper::getValue($this->model, $attr[0]);
        $label = ArrayHelper::getValue($this->model, $this->attribute);

        if (empty($model))
        {
            return $this->emptyMessage;
        }

        if (empty($this->actionControl))
        {
            return $label;
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
            return $label;
        }

        if (is_scalar($label) == FALSE)
        {
            $label = $actionControl->modelLabel();
        }

        if ($actionControl instanceof ActionControl)
        {
            return $actionControl->getLinkTo([
                    'label'       => $label,
                    'linkOptions' => [
                        'title'  => 'click to view this data',
                        'target' => '_blank',
                    ],
            ]);
        }

        return $label;
    }

}