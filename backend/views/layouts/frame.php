<?php
use common\models\BaseConfig;
use common\librarys\Url;
use common\librarys\HtmlHelper;
?>
<div class="top-nav">
    <div class="top-nav-title">
    	<template v-if="frameBaseConfig.<?=BaseConfig::KEY_WEB_NAME ?>">
    		<a href="<?=Url::toRoute('/site/index') ?>">{{frameBaseConfig.<?=BaseConfig::KEY_WEB_NAME ?>}}&nbsp;-&nbsp;后台系统</a>
    	</template>
    </div>
     <el-dropdown class="top-nav-user">
      <div>
        <i class="el-icon-user-solid el-icon--left"></i>{{loginUsername}}<i class="el-icon-caret-bottom el-icon--right"></i>
      </div>
      <el-dropdown-menu slot="dropdown">
       	<el-dropdown-item @click.native="onEditLoginPassword"><i class="el-icon-lock"></i>修改密码</el-dropdown-item>
        <el-dropdown-item @click.native="onLogout" divided><i class="el-icon-switch-button"></i>退出系统</el-dropdown-item>
      </el-dropdown-menu>
    </el-dropdown>
</div>
<div class="left-nav">
    <template v-for="(menu,index1) in leftMenus">
    	<template v-if="!menu.sub || menu.sub.length<=0">
    		<div @click="onSelectLeftMenu(menu)" class="left-nav-item" v-bind:class="{ active: activeLeftMenu&&index1==activeLeftMenu[0] }" slot="reference">
    			<div class="left-nav-icon"><i :class="menu.icon"></i></div>
    			<div class="left-nav-title">{{menu.name}}</div>
    	    </div>
    	</template>
        <el-popover popper-class="left-nav-sub"  :open-delay="200" v-else :width="150" placement="right-start" trigger="hover">
            <div class="left-nav-item" v-bind:class="{ active: activeLeftMenu&&index1==activeLeftMenu[0] }" slot="reference">
    			<div class="left-nav-icon"><i :class="menu.icon"></i></div>
    			<div class="left-nav-title">{{menu.name}}</div>
    		</div>
    		<div v-if="menu.subTitle" class="left-nav-pop-title">{{menu.subTitle}}</div>
    		<el-menu class="left-nav-pop-menu" :default-active="activeLeftMenu&&index1==activeLeftMenu[0]?activeLeftMenu[1]:null">
              <template v-for="(subMenu,index2) in menu.sub">
                  <el-menu-item @click="onSelectLeftMenu(subMenu)" :index="index2">
                    <span slot="title">{{subMenu.name}}</span>
                  </el-menu-item>
              </template>
            </el-menu>
      </el-popover>
    </template>
</div>
<div class="main-container">
	<div class="main-wrap">
    	<div class="main-theme">
    		<div v-if="pagePaths && pagePaths.length>0" class="main-header">
    			<el-breadcrumb separator-class="el-icon-arrow-right">
                  <el-breadcrumb-item v-for="path in pagePaths">
                  	<template v-if="typeof(path) == 'string'">
                  		{{path}}
                  	</template>
                  	<template v-else>
                  		<a :href="path['url']">{{path['name']}}</a>
                  	</template>
                  </el-breadcrumb-item>
                </el-breadcrumb>
    		</div>
    		<div class="main-content">
    			<?=$content ?>
    		</div>
    	</div>
    	<div class="main-footer"><template v-if="frameBaseConfig.<?=BaseConfig::KEY_DEV_NAME ?>">Beginner © {{frameBaseConfig.<?=BaseConfig::KEY_DEV_NAME ?>}}</template></div>
	</div>
</div>
<script>
Vue.$page.mixin.push({
	data:function(){
		var data = {
			frameBaseConfig:<?=HtmlHelper::encodeJson($baseConfig) ?>,
			loginUsername:'<?=HtmlHelper::encodeText(Yii::$app->user->username) ?>',
			pagePaths:<?=HtmlHelper::encodeJson($paths) ?>,
			leftMenus:<?=HtmlHelper::encodeJson($menus) ?>,
			activeLeftMenu:<?=HtmlHelper::encodeJson($activeMenu) ?>,
			editLoginPasswordUrl:'<?=Url::toRoute('/site/edit-login-password') ?>',
			logoutUrl:'<?=Url::toRoute('/auth/logout') ?>'
		};
		return data;
	},
	methods:{
		onSelectLeftMenu:function(item){
			location.href = item.url;
		},
		onEditLoginPassword:function(){
			location.href = this.editLoginPasswordUrl;
		},
		onLogout:function(){
			var _this = this;
			$util.confirm('确认退出系统吗？',{
				type:'warning',
				confirmCallback:function(){
					location.href = _this.logoutUrl;
				}
			});
		}
	}
});
</script>