<?php
use common\librarys\Url;
use common\librarys\HtmlHelper;

$this->title = '设置权限';
$frameConfigs = [
    'paths'=>[
        '系统管理',
        [
            'name'=>'用户管理',
            'url'=>Url::toRoute('manage')
        ],
        $this->title
    ],
    'activeMenu'=>[1,0]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<form-content ref="form" :model="form">
				<div class="set-auth-wrap">
					<set-auth-table ref="checkAuthTable" :items="items" :value="form.rule_id"></set-auth-table>
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
			saveUrl:'<?=Url::toRoute(['','id'=>$model['id']]) ?>',
			saveLoading:false,
			form:{
				rule_id:<?=HtmlHelper::encodeJson($userAuthAssigns) ?>
			},
			items:<?=HtmlHelper::encodeJson($authRuleList) ?>
		}
		return data;
	},
	methods:{
		onSubmit:function(){
			var _this = this;
			this.$refs['form'].submit(this.saveUrl,{
				before:function(form){
					_this.form.rule_id = _this.$refs.checkAuthTable.getValue();
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