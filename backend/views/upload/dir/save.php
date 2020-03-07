<?php
use common\librarys\Url;
use common\librarys\HtmlHelper;
use common\models\UploadDir;

$this->title = !$model?"新建目录":'编辑目录';
$frameConfigs = [
    'paths'=>[
        '上传管理',
        [
            'name'=>'目录管理',
            'url'=>Url::toRoute('manage')
        ],
        $this->title
    ],
    'activeMenu'=>[4,0]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<form-content ref="form" :model="form">
				<div class="form-wrap">
					<div class="form-item-wrap">
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
                  <el-button :disabled="disabled" size="small" type="primary" native-type="submit" @click="onSubmit()" :loading="saveLoading">保存</el-button>
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
			saveUrl:'<?=Url::toRoute(['','id'=>$model['id']]) ?>',
			initDataUrl:'<?=Url::toRoute(['save-form-data','id'=>$model['id']]) ?>',
			saveLoading:false,
			form:<?=HtmlHelper::encodeJson($form)?>,
			nodes: [],
			disabled:<?=$model && $model['type'] != UploadDir::TYPE_CUSTOM?'true':'false'?>
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