<?php

namespace app\controllers;

use Yii;
use app\models\PerpuFunciones;
use app\models\PerpuFuncionesSearch;
use app\models\Vpermisos;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PerpuFuncionesController implements the CRUD actions for PerpuFunciones model.
 */
class PerpuFuncionesController extends Controller {

    public function getAllowed($model = null) {
        $r = true;
        return $r;
    }

    public function ifcanIndex($action, &$nivel, $model = null) {
        $usr = Yii::$app->user->identity->usuar_id;
        $puede = Vpermisos::findBySql(
                        'select * from vpermisos where' . ''
                        . ' (usuar_id = :usr)'
                        . ' and ( permi_clase = :clase or permi_clase = ' . "'*'" . ')'
                        . ' and ( permi_metodo = :action or permi_metodo = ' . "'*'" . ')', ['clase' => 'PerpuFunciones',
                    'usr' => $usr,
                    'action' => $action])->all();
        $r = false;
        if (sizeof($puede) > 0) {
            $r = true;
            $nivel = $puede[0]->permi_nivel;
            if (!is_null($model)) {
                switch ($nivel) {
                    case 0 : {
                            $r = true;
                            break; //puede actura en todos
                        }
                    case 1 : {
                            $r = false;
                            break; //hay que estudiar cada caso por separado 
                        }
                    case 0 : {
                            $r = $model->usuar_id == $usr;  // se presume que la tabla tiene un usuar_id
                        }
                }
            }
        }
        return $r;
    }

    public function ifcan($action, $model = null) {
        $nivel = -1;
        return $this->ifcanIndex($action, $nivel, $model);
    }

    public function can($action, $model = null) {
        $nivel = -1;
        return $this->ifcan($action, $nivel, $model);
    }

    public function canIndex($action, &$nivel, $model = null) {
        if (!$this->ifcanIndex($action, $nivel, $model)) {
            $this->redirect(['site/forbid']);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'add', 'update', 'delete', 'create', 'permisos', 'elimina', 'eliminaAdmin', 'add', 'addBatch'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public static function getdp($searchModel, $nivel) {
        $dataProvider->pagination->pageSize = Yii::$app->params['lineasXgrid'];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        switch ($nivel) {
            case 1 : {
                    break;
                };
            case 2 : {
                    break;
//$dataProvider->query->andFilterWhere(['event_id' => $id,]);
                }
        }
        return $dataProvider;
    }

    /**
     * Lists all PerpuFunciones models.
     * @return mixed
     */
    public function actionIndex() {
        $this->canIndex('index', $nivel);
        $searchModel = new PerpuFuncionesSearch();
        $dataProvider = $this->getdp($searchModel, $nivel);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PerpuFunciones model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id) {
        $this->can('view');
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PerpuFunciones model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $this->can('create');
        $model = new PerpuFunciones();
        if ($model->load(Yii::$app->request->post())) {
            $model->autor = yii::$app->user->identity->getPerso();
            if ($model->save()) {
                return $this->redirect(['perfiles-puesto/view', 'id' => $model->perca_id]);
            } else {// revisar que se hari si no se pudo salvar   
            }
        }
        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * crea en batch nuevosPerpuFunciones modelos hijos de una table.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddBatch($idMaster) {
        $this->can('addBatch');
        $model = new PerpuFunciones();
        if ($model->load(Yii::$app->request->post())) {
            $ri = $model->regis_id;
            //$model->asist_consecutivo = $this->getConsecutivo($ri);
            if (isset($_POST['accion']) and $_POST['accion'] == 'Terminar') {
                // si se de dar d alta al registro uno no se ha registrado ninguno mandar error
                if ($model->asist_consecutivo == 1) {
                    $model = new PerpuFunciones();
                    $model->regis_id = $regis;
                    return $this->render('alta', [
                                'model' => $model, 'regis' => $ri,
                                'warning' => 'Debe dar de alta al menos a un asistente.']);
                }
                $this->redirect(['registro-evento/preregistro', 'regis_id' => $model->regis_id]);
                return;
            }
            if ($model->save()) {
                return $this->redirect(['PerpuFunciones/add', 'regis' => $ri]);
            } else {
                $e = var_dump($model->getErrors());
                Yii::trace('error al asalvar :' . $e);
                throw new NotFoundHttpException('No pudo salvar ' . $model->regis_id . "-" . $e);
            }
        } else {
            $registro = RegistroEvento::findOne(['regis_id' => $regis]);
            // es redundante pero previene algun error
            if (isset($registro)) {
                $model->regis_id = $registro->regis_id;
                return $this->render('addBatch', [
                            'model' => $model, 'idMaster' => $idMaster, 'fieldMaster' => $fieldMaster
                            , 'warning' => ''
                ]);
            } else {
                throw new NotFoundHttpException('No pudo encontrar el registro' . $regis . "-");
            }
        }
    }

    /**
     * crea en batch nuevosPerpuFunciones modelos hijos de una table.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAdd($idMaster) {
        $this->can('add');
        $model = new PerpuFunciones();

        if ($model->load(Yii::$app->request->post())) {
            $model->autor = yii::$app->user->identity->getPerso();
            if ($model->save())
                return $this->redirect(['perfiles-puesto/view', 'id' => $model->perpu_id]);
        }
        else {
            $model->perpu_id = $idMaster;
            return $this->render('add', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PerpuFunciones model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        $this->can('update', $model);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                //return $this->redirect(['perfiles-puesto/view', 'id' => $model->perca_id]);
                return $this->redirect(['perfiles-puesto/view', 'id' => $model->perpu_id]);
            }
        }
        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PerpuFunciones model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $model = $this->findModel($id);
        $perpu_id = $model->perpu_id;
        $this->can('delete', $model);
        $this->findModel($id)->delete();
        return $this->redirect(['perfiles-puesto/view', 'id' => $perpu_id]);
    }

    public function actionEliminaAdmin($id) {
        /** supone un maestro detalle
          $model = $this->findModel($id);
          $this->can('eliminaAdmin', $model);

          $registro = $model->regis_id;
          $this->findModel($id)->delete();
          return $this->redirect(['padre/view', 'id' => $registro,]);
         * 
         */
    }

    public function actionElimina($id) {
        $modelo = $this->findModel($id);
        $registro = $modelo->regis_id;
        $asistente->delete();
        return $this->redirect(['add', 'regis' => $registro,]);
    }

    /**
     * Finds the PerpuFunciones model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PerpuFunciones the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = PerpuFunciones::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
