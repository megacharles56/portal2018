<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Pisos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pisos-formBatch">

    <?php $form = ActiveForm::begin(); ?>

        <?php 
$form->field($model, $fieldMaster)->hiddenInput(["value"=> $idMaster])->label(false); 
 $col = $form->field($model, 'edifi_id')->textInput(); 

 echo $this->twoCols($col, ''); 
 
 $col = $form->field($model, 'piso_nombre')->textInput(['maxlength' => true]); 

 echo $this->twoCols($col, ''); 
?>    <?php $this->finishForm($model , 'prefi_id', $model->prefi_id, $model->isNewRecord);  
 ActiveForm::end(); ?>
</div>