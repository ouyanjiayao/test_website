<?php
use common\librarys\Url;
use common\models\BaseConfig;
use common\librarys\HtmlHelper;

$this->title = "登录";
?>
<body class="login-container">
	<div id="page" v-cloak>
		<el-card class="login-wrap">
			<div class="login-header">
				{{baseConfig.<?=BaseConfig::KEY_WEB_NAME ?>}}&nbsp;-&nbsp;后台登录
			</div>
			<div class="login-center">
    			<el-form ref="form" :model="form" :rules="rules" @submit.native.prevent >
    				<el-form-item prop="username">
    			    	<el-input prefix-icon="el-icon-user-solid" placeholder="用户名" v-model="form.username" ></el-input>
    			  	</el-form-item>
    			  	<el-form-item prop="password">   
    			    	<el-input prefix-icon="el-icon-lock" placeholder="密码" v-model="form.password" type="password"></el-input>
    			  	</el-form-item>
    			  	<el-form-item class="login-captcha-form-item" prop="captcha">
        			    <el-input prefix-icon="el-icon-picture"  placeholder="验证码" v-model="form.captcha">
        			    </el-input>
        			    <img :src="captchaSrc" @click="loadCaptcha"/>
    			  	</el-form-item>
    				<el-button type="primary" class="login-btn" @click="onSubmit" native-type="submit" :loading="loading">登录</el-button>
    			</el-form>
			</div>
			<div class="login-footer">
			Beginner © {{baseConfig.<?=BaseConfig::KEY_DEV_NAME ?>}}
			</div>
		</el-card>
	</div>
</body>
<script>
Vue.$page.mixin.push({
	data : function() {
		var data = {
			siteUrl:'<?=Url::toRoute('/site/index') ?>',
			captchaUrl:'<?=Url::toRoute('captcha') ?>',
			baseConfig:<?=HtmlHelper::encodeJson($baseConfig) ?>,
			loading:false,
			form:{},
			rules: {
                username: [
                	{ required: true, message: '请输入用户名'}
                ],
                password: [
                	{ required: true, message: '请输入密码'}
                ],
                captcha: [
                	{ required: true, message: '请输入验证码'}
                ]
		    },
		    captchaSrc:''
		};
		return data;
	},
	created:function(){
		this.loadCaptcha();
	},
	methods : {
		onSubmit:function(){
			var _this = this;
			this.$refs.form.validate(function(valid){
				if(!valid)
				{
					return;
				}
				_this.loading = true;
				$util.request('<?=Url::toRoute('') ?>',{
					method:'post',
					data:_this.form,
					success:function(response)
					{
						if(response.data.success)
						{
							location.href = _this.siteUrl;
						}else{
							_this.loadCaptcha();
							_this.$message.error(response.data.message);
						}
					},
					error:function(){
						_this.loadCaptcha();
					},
					complete:function(){
						_this.loading = false;
					}
				});
			});
		},
		loadCaptcha:function(){
			this.captchaSrc = $util.url.stringify(this.captchaUrl,{t:new Date().getTime()});
		}
	}
});
</script>