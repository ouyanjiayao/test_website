<?php
use common\librarys\HtmlHelper;

$this->title = "主页";
$frameConfigs = [
    'paths'=>[
        $this->title 
    ],
    'activeMenu' => [
        0
    ]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<div class="index-container">
				<div class="welcome-wrap">
					<div class="welcome-title">欢迎使用 ~ 超级管理员 {{user}}</div>
    			</div>
    			<div v-if="isSuperAdmin" class="index-block">
        			<div class="index-block-title">开发者功能</div>
        			<div class="index-block-content">
        				<el-button icon="el-icon-link" size="small" type="warning">权限规则</el-button>
        				<el-button icon="el-icon-s-tools" size="small" type="warning">基本配置</el-button>
        			</div>
    			</div>
    			<div class="index-block">
        			<div class="index-block-title">系统功能</div>
        			<div class="index-block-content">
            			<el-button @click="onEditLoginPassword" icon="el-icon-lock" size="small" type="primary">修改密码</el-button>
            			<el-button @click="onLogout" icon="el-icon-switch-button" size="small" type="danger" >退出系统</el-button>
        			</div>
    			</div>
    			<div v-html="page"></div>
			</div>
		<?=$this->endContent() ?>
	</div>
</body>
<script>
Vue.$page.mixin.push({
	data:function(){
		var data = {
			isSuperAdmin:<?=Yii::$app->user->isSuperAdmin()?'true':'false' ?>,
			user:'<?=HtmlHelper::encodeText(Yii::$app->user->username) ?>',
			page:''
		}
		return data;
	},
	methods:{
		test:function(){
			this.page = '<el-button icon="el-icon-link" size="small" type="warning">权限规则</el-button>';
		}
	}
});
</script>