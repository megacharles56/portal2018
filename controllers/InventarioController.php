<?php

namespace app\controllers;

use Yii;
use app\models\Inventario;
use app\models\InventarioSearch;
use app\models\Vpermisos;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * InventarioController implements the CRUD actions for Inventario model.
 */
class InventarioController extends Controller {

    public function getAllowed($model = null) {
        $r = true;
        return $r;
    }

    public function ifcanIndex($action, &$nivel, $model = null) {
        $usr = Yii::$app->user->identity->usuar_id;
        $puede = Vpermisos::findBySql(
                        'select * from vpermisos where' . ''
                        . ' (usuar_id = :usr)'
                        . ' and ( permi_clase = :clase or permi_clase = '. "'*'".')'
                        . ' and ( permi_metodo = :action or permi_metodo ='. "'*'".')', ['clase' => 'Inventario',
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
                    case 2 : {
                            //$r = $model->usuar_id == $usr;  // se presume que la tabla tiene un usuar_id
                            if($nivel == 2 ){
                                //echo "Es NIVEL 2".$nivel;
                                $this->redirect(['site/forbid']);
                            }
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
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
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = Yii::$app->params['lineasXgrid'];
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
     * Lists all Inventario models.
     * @return mixed
     */
    public function actionIndex() {
        $this->canIndex('index', $nivel);
        $searchModel = new InventarioSearch();
        $dataProvider = $this->getdp($searchModel, $nivel);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Inventario model.
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
     * Creates a new Inventario model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $this->can('create');
        $model = new Inventario();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->inven_id]);
            } else {// revisar que se hari si no se pudo salvar   
            }
        }
        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * crea en batch nuevosInventario modelos hijos de una table.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddBatch($idMaster) {
        $this->can('addBatch');
        $model = new Inventario();
        if ($model->load(Yii::$app->request->post())) {
            $ri = $model->regis_id;
            //$model->asist_consecutivo = $this->getConsecutivo($ri);
            if (isset($_POST['accion']) and $_POST['accion'] == 'Terminar') {
                // si se de dar d alta al registro uno no se ha registrado ninguno mandar error
                if ($model->asist_consecutivo == 1) {
                    $model = new Inventario();
                    $model->regis_id = $regis;
                    return $this->render('alta', [
                                'model' => $model, 'regis' => $ri,
                                'warning' => 'Debe dar de alta al menos a un asistente.']);
                }
                $this->redirect(['registro-evento/preregistro', 'regis_id' => $model->regis_id]);
                return;
            }
            if ($model->save()) {
                return $this->redirect(['Inventario/add', 'regis' => $ri]);
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
     * crea en batch nuevosInventario modelos hijos de una table.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAdd($idMaster) {
        $this->can('add');
        $model = new Inventario();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save())
                return $this->redirect(['Inventario/view', 'id' => $model->perca_id]);
        }
        else {
            $model->perca_id = $perca_id;
            return $this->render('add', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Inventario model.
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
                return $this->redirect(['view', 'id' => $model->inven_id]);
            }
        }
        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Inventario model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $model = $this->findModel($id);
        $this->can('delete', $model);
        $this->findModel($id)->delete();
        /*
          $model->perca_estado = 'inactivo';
          $model->save();
         */
        return $this->redirect(['index']);
        ;
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
     * Finds the Inventario model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Inventario the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Inventario::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
