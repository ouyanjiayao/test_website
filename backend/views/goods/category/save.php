<?php
use common\librarys\Url;
use common\models\GoodsCategory;
use common\librarys\HtmlHelper;

$this->title = !$model?"新建分类":'编辑分类';
$frameConfigs = [
    'paths'=>[
        '商品管理',
        [
            'name'=>'分类管理',
            'url'=>Url::toRoute('manage').'#type='.($model?$model['type']:Yii::$app->request->get('type'))
        ],
        $this->title
    ],
    'activeMenu'=>[2,0]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<form-content ref="form" :model="form">
				<div class="form-wrap">
					<div class="form-item-wrap">
    					<el-form-item label="类型">
    						<div class="form-item-inner-text">{{typeName}}</div>
            			 </el-form-item>
        				<el-form-item label="名称">
            			    <el-input v-model="form.name"></el-input>
            			 </el-form-item>
            			 <el-form-item label="父节点">
            			 	<el-cascader v-model="form.parent_id"
            			 		:clearable=true
                                :options="nodes"
                                :props="{ expandTrigger: 'click',emitPath:false,checkStrictly:true }"
                                ></el-cascader>
            			 </el-form-item>
            			 <el-form-item label="顺序">
            			 	<el-input-number v-model="form.sort"></el-input-number>
            			 </el-form-item>
        			 </div>
              	</div>
              	<el-button-group slot="toolbar">
                  <el-button size="small" type="primary" native-type="submit" @click="onSubmit()" :loading="saveLoading">保存</el-button>
                </el-button-group>
			</form-content>
		<?=$this->endContent() ?>
	</div>
</body>
<script>
Vue.$page.mixin.push({
	data:function(){
		var _this = this;
		var data = {
			saveUrl:'<?=Url::toRoute(['','id'=>$model['id'],'type'=>!$model?Yii::$app->request->get('type'):null]) ?>',
			initDataUrl:'<?=Url::toRoute(['save-form-data','id'=>$model['id'],'type'=>$model?$model['type']:Yii::$app->request->get('type')]) ?>',
			saveLoading:false,
			form:<?=HtmlHelper::encodeJson($form)?>,
			typeName:'<?=GoodsCategory::$typeMap[$model?$model['type']:Yii::$app->request->get('type')] ?>',
			nodes: []
		}
		return data;
	},
	mounted:function(){
		var _this = this;
		this.$refs['form'].requestInitData(this.initDataUrl,{
			success:function(response){
				_this.nodes = response.data.nodes;
			}
		});
	},
	methods:{
		onSubmit:function(){
			var _this = this;
			this.$refs['form'].submit(this.saveUrl,{
				before:function(){
					_this.saveLoading = true;
				},
				complete:function(){
					_this.saveLoading = false;
				}
			});
		}
	}
});
</script>