<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Vpermisos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="vpermisos-formAdd">

    <?php $form = ActiveForm::begin(); ?>

        <?php   $col = $form->field($model, 'usuar_id')->textInput(); 

 echo $this->twoCols($col, ''); 
  $col = $form->field($model, 'rol_nombre')->textInput(['maxlength' => true]); 

 echo $this->twoCols($col, ''); 
  $col = $form->field($model, 'permi_clase')->textInput(['maxlength' => true]); 

 echo $this->twoCols($col, ''); 
  $col = $form->field($model, 'metodo')->textInput(['maxlength' => true]); 

 echo $this->twoCols($col, ''); 
  $col = $form->field($model, 'campo')->textInput(['maxlength' => true]); 

 echo $this->twoCols($col, ''); 
  $col = $form->field($model, 'nivel')->textInput(); 

 echo $this->twoCols($col, ''); 
?>    <?php $this->finishForm($model );  
 ActiveForm::end(); ?>
</div>