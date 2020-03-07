<?php
use common\librarys\Url;
use app\models\SystemUser;

$this->title = "用户管理";
$frameConfigs = [
    'paths'=>[
        '系统管理',
        $this->title
    ],
    'activeMenu'=>[1,0]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
		  <data-girdview :select-rows.sync="selectRows" row-key="id" ref="dataGirdview" :url="listUrl" :search-form="searchForm" :hash-query="true">
		  	<template slot="searchForm">
                <el-form-item label="关键词">
    				<el-input v-model="searchForm.model.keyword"></el-input>
    		    </el-form-item>
		  	</template>
		  	<template slot="toolbar">
		  		<el-button-group>
            		<el-button :disabled="!createUrl" size="small" type="primary" @click="onCreate">新建</el-button>
                    <el-button :disabled="!deleteUrl || selectRows.length<=0" size="small" type="danger" @click="onDelete" :loading="deleteLoading">删除</el-button>
				</el-button-group>
		  	</template>
		  	<el-table-column type="selection"></el-table-column>
		  	<el-table-column prop="id" label="ID" min-width="10%"></el-table-column>
            <el-table-column prop="username" label="用户名" min-width="25%"></el-table-column>
            <el-table-column prop="state" label="状态" min-width="20%">
            	<template slot-scope="scope">
            		<el-tag v-if="scope.row.state.value == <?=SystemUser::STATE_DISABLE ?>" type="danger">{{scope.row.state.label}}</el-tag>
            		<el-tag v-else-if="scope.row.state.value == <?=SystemUser::STATE_ENABLE ?>" type="success">{{scope.row.state.label}}</el-tag>
            	</template>
            </el-table-column>
            <el-table-column prop="created_time" label="创建时间" min-width="25%"></el-table-column>
            <el-table-column prop="remark" label="备注" min-width="20%"></el-table-column>
            <el-table-column width="250" align="right">
            	<template slot-scope="scope">
            		<el-button-group>
                		<el-button :disabled="!editUrl" size="small" type="primary" @click="onEdit(scope.row)">编辑</el-button>
                		<el-button size="small" type="primary" :disabled="!setAuthUrl || disableSetAuth(scope.row)" @click="onSetAuth(scope.row)">权限</el-button>
                	</el-button-group>
            	</template>
            </el-table-column>
		  </data-girdview>
		<?=$this->endContent() ?>
	</div>
</body>
<script>
Vue.$page.mixin.push({
	data:function(){
		var data = {
			listUrl:'<?=Yii::$app->user->checkAccess('list-data')?Url::toRoute('list-data'):'' ?>',
			createUrl:'<?=Yii::$app->user->checkAccess('create')?Url::toRoute('create'):'' ?>',
			editUrl:'<?=Yii::$app->user->checkAccess('edit')?Url::toRoute('edit'):'' ?>',
			setAuthUrl:'<?=Yii::$app->user->checkAccess('set-auth')?Url::toRoute('set-auth'):'' ?>',
			deleteUrl:'<?=Yii::$app->user->checkAccess('delete')?Url::toRoute('delete'):'' ?>',
			deleteLoading:false,
			searchForm:{
				default:function(){
					return {
						'keyword':''
					};
				}
			},
			selectRows:[]
        }
		data.searchForm.model = data.searchForm.default();
		return data;
	},
	methods:{
		onCreate:function(){
			location.href = this.createUrl;
		},
		onEdit:function(row){
			location.href = $util.url.stringify(this.editUrl,{id:row.id});
		},
		onSetAuth:function(row){
			location.href = $util.url.stringify(this.setAuthUrl,{id:row.id});
		},
		onDelete:function(){
			var _this = this;
			this.$refs['dataGirdview'].submit(this.deleteUrl,{
				confirm:'确认删除所选记录？',
				before:function(){
					_this.deleteLoading = true;
				},
				complete:function(response){
					_this.deleteLoading = false;
				}
			});
		},
		disableSetAuth:function(row){
			if(row['type'] == <?=SystemUser::TYPE_SUPER_ADMIN ?>)
			{
				return true;
			}
			if(row['id'] == <?=Yii::$app->user->id ?>)
			{
				return true;
			}
			return false;
		}
	}
});
</script>