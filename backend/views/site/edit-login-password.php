<?php
use common\librarys\Url;
use app\models\SystemUser;

$this->title = "修改密码";
$frameConfigs = [
    'paths'=>[
        $this->title 
    ]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<form-content ref="form" :rules="rules" :model="form">
				<div class="form-wrap">
					<div class="form-item-wrap">
        				<el-form-item label="用户">
        					<div class="form-item-inner-text">{{loginUsername}}</div>
                      	</el-form-item>
        				<el-form-item prop="old_password" label="旧密码">
                        	<el-input v-model="form.old_password" type='password' :maxlength='passwordMaxLength'></el-input>
                      	</el-form-item>
                      	<el-form-item prop="new_password" label="新密码">
                        	<el-input v-model="form.new_password" type='password' :maxlength='passwordMaxLength'></el-input>
                      	</el-form-item>
                      	<el-form-item prop="comfirm_password" label="确认密码">
                        	<el-input v-model="form.comfirm_password" type='password' :maxlength='passwordMaxLength'></el-input>
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
			saveUrl:'<?=Url::toRoute() ?>',
			saveLoading:false,
			form:{},
			passwordMaxLength:<?=SystemUser::PASSWORD_MAX_LENGTH ?>,
			rules:{
				  'old_password': [
		            	{ required: true, message: '请输入密码'}
		          ],
		          'new_password': [
    		            { required: true, message: '请输入密码'},
    		            { min: <?=SystemUser::PASSWORD_MIN_LENGTH ?>, message:'最小长度为<?=SystemUser::PASSWORD_MIN_LENGTH ?>个字符'},
      		        	{ 
    	  		        	validator:function(rule, value, callback){
      	  		  				value === _this.form['old_password']?callback(new Error()):callback();
      	  		  			}, 
      	  		  			message: '新密码不能与旧密码相同'
      	  	  		  	}
		          ],
   		      	  'comfirm_password': [
    					{ required: true, message: '请输入密码'},
    		            { 
    			            validator:function(rule, value, callback){
    			    			value !== _this.form['new_password']? callback(new Error()):callback();
    			    		},
    			            message: '确认密码不一致'
    				    }
		          ]
			}
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