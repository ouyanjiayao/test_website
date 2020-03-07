<?php
use common\librarys\Url;
use common\models\GoodsTag;
use common\librarys\HtmlHelper;

$this->title = !$model?"新建标签":'编辑标签';
$frameConfigs = [
    'paths'=>[
        '商品管理',
        [
            'name'=>'标签管理',
            'url'=>Url::toRoute('manage')
        ],
        $this->title
    ],
    'activeMenu'=>[2,1]
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
        			 </div>
        			 <div v-if="info" class="blockquote"><span class="blockquote-title">有赞同步</span></div>
    			 	<el-form-item v-if="info && info.youzan_syn_state" label="有赞ID">
    					<div class="form-item-inner-text">{{info.youzan_id}}</div>
        			 </el-form-item>
    				<el-form-item v-if="info && info.youzan_syn_state" label="本地版本">
    					<div class="form-item-inner-text">{{info.version}}</div>
        			 </el-form-item>
        			 <el-form-item v-if="info && info.youzan_version" label="有赞版本">
    					<div class="form-item-inner-text">{{info.youzan_version}}</div>
        			 </el-form-item>
        			 <el-form-item v-if="info && info.youzan_syn_time" label="同步时间">
    					<div class="form-item-inner-text">{{info.youzan_syn_time}}</div>
        			 </el-form-item>
        			 <el-form-item v-if="info && info.youzan_syn_state" label="同步状态">
    					<div class="form-item-inner-text">{{info.youzan_syn_state}}</div>
        			 </el-form-item>
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
			saveLoading:false,
			disabled:<?=$model['type'] == GoodsTag::TYPE_SYSTEM?'true':'false'?>,
			form:<?=HtmlHelper::encodeJson($saveForm) ?>,
			info:<?=HtmlHelper::encodeJson($info) ?>
		}
		return data;
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