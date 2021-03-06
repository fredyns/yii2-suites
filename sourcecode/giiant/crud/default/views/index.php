<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/*
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass();
$model->setScenario('crud');

$modelName = Inflector::camel2words(Inflector::pluralize(StringHelper::basename($model::className())));
$safeAttributes = $model->safeAttributes();

if (empty($safeAttributes)) {
    /** @var \yii\db\ActiveRecord $model */
    $model = new $generator->modelClass();
    $safeAttributes = $model->safeAttributes();
    if (empty($safeAttributes)) {
        $safeAttributes = $model->getTableSchema()->columnNames;
    }
}

$actioncontrolPath = ltrim($generator->modelClass, '\\').'ActControl';
$actioncontrolPath = str_replace('models', 'actioncontrols', $actioncontrolPath);
$actioncontrolClass = StringHelper::basename($actioncontrolPath);

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\helpers\Url;
use <?= $generator->indexWidgetType === 'grid' ? $generator->indexGridClass : 'yii\\widgets\\ListView' ?>;
use cornernote\returnurl\ReturnUrl;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
<?php if ($generator->searchModelClass !== ''): ?>
/* @var $searchModel <?= ltrim($generator->searchModelClass, '\\') ?> */
<?php endif; ?>

$this->title = Yii::t(<?= "'{$generator->modelMessageCategory}', '{$modelName}'" ?>);
$this->params['breadcrumbs'][] = $this->title;
<?php if($generator->accessFilter): ?>

/**
 * create action column template depending acces rights
 */
$actionColumnTemplates = [];

if (\Yii::$app->user->can('<?=$permisions['view']['name']?>', ['route' => true])) {
    $actionColumnTemplates[] = '{view}';
}

if (\Yii::$app->user->can('<?=$permisions['update']['name']?>', ['route' => true])) {
    $actionColumnTemplates[] = '{update}';
}

if (\Yii::$app->user->can('<?=$permisions['delete']['name']?>', ['route' => true])) {
    $actionColumnTemplates[] = '{delete}';
}
<?php endif; ?>
<?php echo '?>'; ?>

<div class="giiant-crud <?= Inflector::camel2id(StringHelper::basename($generator->modelClass), '-', true) ?>-index">

    <h1>
        <?= "<?= Yii::t('{$generator->modelMessageCategory}', '{$modelName}') ?>\n" ?>
        <small class="badge">
            List
        </small>
    </h1>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?php " ?>//echo $this->render('_search', ['model' =>$searchModel]); ?>
<?php else: ?>
    <?= "<?= " ?>$this->render('_search', ['model' =>$searchModel]); ?>
<?php endif; ?>

    <?= "<?php\n"; ?>
    \yii\widgets\Pjax::begin([
        'id' => 'pjax-main', 
        'enableReplaceState' => false, 
        'linkSelector' => '#pjax-main ul.pagination a, th a', 
        'clientOptions' => [
            'pjax:success' => 'function(){alert(\"yo\")}',
        ],
    ]);
    ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>

    <div class="table-responsive">
        <?= "<?=\n" ?>
        GridView::widget([
            'dataProvider' => $dataProvider,
            'pager' => [
                'class' => yii\widgets\LinkPager::className(),
                'firstPageLabel' => <?= $generator->generateString('First') ?>,
                'lastPageLabel' => <?= $generator->generateString('Last').",\n" ?>
            ],
<?php if ($generator->searchModelClass !== ''): ?>
            'filterModel' => $searchModel,
<?php endif; ?>
            'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
            'headerRowOptions' => ['class' => 'x'],
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'class' => 'fredyns\suite\grid\KartikViewColumn',
                    'actionControl' => '<?= $actioncontrolPath; ?>',
                    'attribute' => '<?= $generator->getModelLabel(); ?>',
                ],
<?php

                $count = 0;
                $hidenAttributes = [
                    'id',
                    'recordStatus',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                    'deleted_at',
                    'deleted_by',
                    $generator->getModelLabel(),
                ];
                $safeAttributes = array_diff($safeAttributes, $hidenAttributes);

                foreach ($safeAttributes as $attribute) {
                    $format = trim($generator->columnFormat($attribute, $model));
                
                    if ($format == false) {
                        continue;
                    }
                
                    if (++$count < $generator->gridMaxColumns) {
                        echo "                " . str_replace("\n", "\n                ", $format) . ",\n";
                    } else {
                        echo "                /*/\n                " 
                            .trim(str_replace("\n", "\n                ", $format)) 
                            .",\n                //*/\n";
                    }
                }

                ?>
                [
                    'class' => 'fredyns\suite\grid\KartikActionColumn',
                    'actionControl' => '<?= $actioncontrolPath; ?>',
                ],        
            ],
            'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => FALSE, // pjax is set to always true for this demo
            'toolbar' => [
                $actionControl->button('create').' {export}',
            ],
            'export' => [
                'icon' => 'export',
                'label' => 'Export',
            ],
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'hover' => true,
            'showPageSummary' => true,
            'pageSummaryRowOptions' => [
                'class' => 'kv-page-summary',
                'style' => 'height: 100px;'
            ],
            'persistResize' => false,
            'exportConfig' => [
                GridView::EXCEL => [
                    'label' => 'Save as EXCEL',
                    'filename' => '<?= $modelName ?>',
                ],
                GridView::PDF => [
                    'label' => 'Save as PDF',
                    'filename' => '<?= $modelName ?>',
                ],
            ],
            'panel' => [
                'type' => GridView::TYPE_PRIMARY,
            ],
            'panelBeforeTemplate' => '
                <div class="pull-right">
                    <div class="btn-toolbar kv-grid-toolbar" role="toolbar">
                        {toolbar}
                    </div>
                </div>
                <div class="pull-left">
                    <div class="kv-panel-pager">
                        {pager}
                    </div>
                </div>
                {before}
                <div class="clearfix"></div>
            ',
        ]); 
        ?>

    </div>
<?php else: ?>

    <?= '<?= ' ?> 
    ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]);
    ?>
<?php endif; ?>

    <?= "<?php "; ?>\yii\widgets\Pjax::end(); ?>

</div>
