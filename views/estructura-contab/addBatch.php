<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\EstructuraContab */

$this->title = 'Crear Estructura Contab';

$this->params['breadcrumbs'][] = ['label' => 'Estructura Contabs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="estructura-contab-add">
  
   <div class='row'> <div  class='col-md-4'>
            <h1>   <?= Html::encode($this->title) ?></h1>
            
        </div>
        <div class="col-md-6">
            <h2 style="font-size: 1.4em">Ingrese el nombre del participante y presione agregar, una vez esten todos en la tabla superior presione terminar.</h2>
        </div>
    </div>
     <?php
    echo
    $this->render('_formAddBatch', [
        'model' => $model, 'idMaster'=>$idMaster, 'fieldMaster'=>$fieldMaster, 'warning'=> $warning
    ])
    ?></div>
