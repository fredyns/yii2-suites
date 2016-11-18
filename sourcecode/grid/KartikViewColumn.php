<?php

namespace fredyns\suites\grid;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Generate link to view detailed data model for Kartik-Grid
 *
 *
 * @author Fredy Nurman Saleh <email@fredyns.net>
 */
class KartikViewColumn extends \kartik\grid\DataColumn
{
    /**
     * @inheritdoc
     */
    public $format = 'html';

    /**
     * Action Control class name
     *
     * @var string
     */
    public $actionControl = 'fredyns\suites\libraries\ActionControl';

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $label         = ArrayHelper::getValue($model, $this->attribute, 'view');
        $actionControl = Yii::createObject([
                'class' => $this->actionControl,
                'model' => $model,
        ]);

        return $actionControl->getLinkTo($label);
    }

}