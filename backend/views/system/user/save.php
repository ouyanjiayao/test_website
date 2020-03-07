<?php
use common\librarys\Url;
use app\models\SystemUser;
use common\librarys\HtmlHelper;

$this->title = !$model?"新建用户":'编辑用户';
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
			<form-content ref="form" :rules="rules" :model="form">
				<div class="form-wrap">
					<div class="form-item-wrap">
        				<el-form-item prop="username" label="用户名">
            			    <el-input :disabled="disable.username" v-model="form.username"></el-input>
            			 </el-form-item>
            			 <el-form-item prop="password" label="密码">
            			    <el-input :disabled="disable.password" type="password" v-model="form.password" :maxlength="passwordMaxLength"></el-input>
            			 </el-form-item>
            			  <el-form-item label="备注">
            			    <el-input :disabled="disable.remark" type="textarea" v-model="form.remark"></el-input>
            			 </el-form-item>
            			 <el-form-item label="状态">
            			   <el-select :disabled="disable.state" v-model="form.state">
                            <el-option v-for="option in stateOptions" :label="option.label" :value="option.value" :key="option.value">
                           </el-option>
                          </el-select>
            			 </el-form-item>
            			 <el-form-item v-if="createdTime" label="创建时间">
            			   	<div class="form-item-inner-text">{{createdTime}}</div>
            			 </el-form-item>
        			 </div>
              	</div>
              	<el-button-group slot="toolbar">
                  <el-button :disabled="disable.save" size="small" type="primary" native-type="submit" @click="onSubmit()" :loading="saveLoading">保存</el-button>
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
			isCreate:<?=!$model?'true':'false' ?>,	
			saveUrl:'<?=Url::toRoute(['','id'=>$model['id']]) ?>',
			saveLoading:false,
			form:<?=HtmlHelper::encodeJson($saveForm) ?>,
			disable:<?=HtmlHelper::encodeJson($saveFormDisable)  ?>,
			createdTime:'<?=$model['created_time']?date('Y/m/d H:i',$model['created_time']):'' ?>',
			passwordMaxLength:<?=SystemUser::PASSWORD_MAX_LENGTH ?>,
			rules:{
				'username':[],
				'password': [
		            { min: <?=SystemUser::PASSWORD_MIN_LENGTH ?>, message:'最小长度为<?=SystemUser::PASSWORD_MIN_LENGTH ?>个字符'}
		          ]
			},
			stateOptions: <?=HtmlHelper::encodeMapOptions(SystemUser::$stateMap) ?>
		}
		if(data.isCreate){
			data.rules['username'].push({ required: true, message: '请输入用户名'});
			data.rules['password'].push({ required: true, message: '请输入密码'});
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