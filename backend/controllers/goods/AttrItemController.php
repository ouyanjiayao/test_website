<?php
namespace app\controllers\goods;

use Yii;
use app\components\BaseController;
use app\services\GoodsAttrItemService;
use common\models\GoodsAttr;
use common\models\GoodsAttrItem;
use common\models\GoodsAttrItemAssign;
use common\librarys\ArrayHelper;
use common\models\Goods;

class AttrItemController extends BaseController
{

    public function behaviors()
    {
        return [
            [
                'class' => 'app\components\AccessControlFilter'
            ],
            [
                'class' => 'yii\filters\VerbFilter',
                'actions' => [
                    'fast-create' => [
                        'post'
                    ]
                ]
            ]
        ];
    }

    public $goodsAttrItemService = null;

    public function init()
    {
        $this->goodsAttrItemService = new GoodsAttrItemService();
    }

    public function actionGetOptions()
    {
        $orderBy = null;
        $params = [];
        $attrId = Yii::$app->request->get('attr_id');
        /*if($attrId == GoodsAttr::SYSTEM_HANDLE_ID){
            $orderBy = [new \yii\db\Expression('FIELD (id, 117) desc,id desc')];
        }*/
        return $this->asJson($this->goodsAttrItemService->getOptions('id', 'name', [
            'attr_id' => $attrId,
            'type'=>GoodsAttrItem::TYPE_CUSTOM
        ],$orderBy));
    }

    public function actionFastCreate()
    {
        $json = [];
        $systemAttrId = Yii::$app->request->get('system_attr_id');
        if (! $systemAttrId) {
            $model = $this->goodsAttrItemService->fastCreate(Yii::$app->request->post('value'), Yii::$app->request->post('attr_id'));
        } else if ($systemAttrId == GoodsAttr::SYSTEM_WEIGHT_ID) {
            $model = $this->goodsAttrItemService->fastCreate_weight(Yii::$app->request->post('value'),Yii::$app->request->post('desc'));
            $json['weight'] = $model['weight'];
        }
        $json['value'] = $model['id'];
        $json['label'] = $model['name'];
        return $this->asJson($json);
    }
    
    /*public function actionTest(){
        $models = GoodsAttrItem::find()->andWhere('weight is not null')->all();
        foreach($models as $model)
        {
            preg_match_all("/（(.*)）/",$model['name'],$test);
            if($test[1])
            {
                $desc = $test[1][0];
            }
            $name = $this->test($model['weight'], $desc);
            $model['name'] = $name;
            $model->save();
            $itemAssigns = GoodsAttrItemAssign::find()->asArray()->andWhere(['item_id'=>$model['id']])->all();
            foreach($itemAssigns as $itemAssign)
            {
               $goods= Goods::find()->andWhere(['id'=>$itemAssign['goods_id']])->one();
               if($goods)
               {
                   $goods['version'] = $goods['version'] + 1;
                   $goods->save();
                   echo $goods['id'].'<br/>';
               }
            }
        }
    }
    
    public function test($value,$desc)
    {
        $name = "约{$value}g";
        $descArray = [];
        if($value<500)
        {
            if($value == 250)
            {
                $descArray[] = '半斤';
            }else{
                $descArray[] = ($value/50) .'两';
            }
        }else{
            $jin = number_format($value/500,1);
            $jinA = explode('.',(string)$jin);
            if($jinA[1]==5)
            {
                $descArray[] = $jinA[0]. "斤半";
            }else{
                $descArray[] = floatval($jin). "斤";
            }
        }
        if($desc)
        {
            $descArray[] = $desc;
        }
        $desc = implode(',',$descArray);
        if($desc)
        {
            $name.= "({$desc})";
        }
        return $name;
    }*/
}
