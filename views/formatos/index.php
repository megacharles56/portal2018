<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\FormatosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Formatos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="formatos-index">
    <?php
    $columnData = [['class' => 'yii\grid\SerialColumn'],
        'forma_nombre',
    ];
    /// aqui se debe evaluar si siempre hacerlo o no, el rendimiento es muy bajo
    $template = $this->getBtnTemplate(true);

    if ($template <> '') {
        $colBtn = [['class' => 'yii\grid\ActionColumn', 'template' => $template]];
        $cols = array_merge($columnData, $colBtn);
    } else {
        $cols = $columnData;
    }

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $cols
    ]);
    ?>
</div>
