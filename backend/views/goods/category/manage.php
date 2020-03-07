<?php
use common\librarys\Url;
use common\models\GoodsCategory;
use common\librarys\HtmlHelper;

$this->title = "分类管理";
$frameConfigs = [
    'paths'=>[
        '商品管理',
        $this->title
    ],
    'activeMenu'=>[2,0]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
		  <data-girdview :select-rows.sync="selectRows" row-key="id" ref="treeGirdview" :hash-query="true" :url="listUrl" :is-tree=true :request-params="listRequestParams">
		  	<el-tabs v-model="listRequestParams.model.type" slot="headerContent" type="card" @tab-click="onChangeTab">
                <el-tab-pane v-for="option in tabOptions" :label="option.label" :name="option.value"></el-tab-pane>
              </el-tabs>
		  	<template slot="toolbar">
		  		<el-button-group>
            		<el-button :disabled="!createUrl" size="small" type="primary" @click="onCreate">新建</el-button>
                    <el-button :disabled="!deleteUrl || selectRows.length<=0" size="small" type="danger" @click="onDelete" :loading="deleteLoading">删除</el-button>
				</el-button-group>
		  	</template>
		  	<el-table-column type="selection"></el-table-column>
		  	<el-table-column prop="id" label="ID" min-width="10%"></el-table-column>
		  	<tree-expand-column prop="name" label="名称" min-width="90%"></tree-expand-column>
            <el-table-column width="250" align="right">
            	<template slot-scope="scope">
            		<el-button-group>
                		<el-button :disabled="!editUrl" size="small" type="primary" @click="onEdit(scope.row)">编辑</el-button>
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
			deleteUrl:'<?=Yii::$app->user->checkAccess('delete')?Url::toRoute('delete'):'' ?>',
			deleteLoading:false,
			selectRows:[],
			listRequestParams:{
				default:function(){
					return {
						type:'<?=GoodsCategory::TYPE_DP ?>'
					};
				}
			},
			tabOptions:<?=HtmlHelper::encodeMapOptions(GoodsCategory::$typeMap) ?>
        }
		data.listRequestParams.model = data.listRequestParams.default();
		return data;
	},
	methods:{
		onCreate:function(){
			location.href = $util.url.stringify(this.createUrl,{type:this.listRequestParams.model.type});;
		},
		onEdit:function(row){
			location.href = $util.url.stringify(this.editUrl,{id:row.id});
		},
		onDelete:function(){
			var _this = this;
			this.$refs['treeGirdview'].submit(this.deleteUrl,{
				confirm:'确认删除所选记录？',
				before:function(){
					_this.deleteLoading = true;
				},
				complete:function(response){
					_this.deleteLoading = false;
				}
			});
		},
		onChangeTab:function(tab){
			this.$refs['treeGirdview'].setRequestParams({
				'type':tab.name
			});
		}
	}
});
</script>