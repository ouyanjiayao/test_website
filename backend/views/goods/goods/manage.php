<?php
use common\librarys\Url;
use common\librarys\HtmlHelper;
use common\models\Goods;

$this->title = "商品管理";
$frameConfigs = [
    'paths'=>[
        '商品管理',
        $this->title
    ],
    'activeMenu'=>[2,2]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
		  <data-girdview :select-rows.sync="selectRows" row-key="id" ref="dataGirdview" :url="listUrl" :search-form="searchForm" :hash-query="true" :request-params="listRequestParams">
		  	<template slot="searchForm">
                <el-form-item label="关键词">
    				<el-input v-model="searchForm.model.keyword"></el-input>
    		    </el-form-item>
    		    <el-form-item label="分类">
    		    	<el-cascader v-model="searchForm.model.category_id" :clearable=true :options="categoryNodes[this.listRequestParams.model.type]" :props="{ expandTrigger: 'click',emitPath:false,checkStrictly:true }"></el-cascader>
    		    </el-form-item>
    		    <el-form-item label="标签">
    		    	<el-select v-model="searchForm.model.tag_id" clearable>
                        <el-option v-for="option in tagOptions" :label="option.label" :value="option.value" :key="option.value">
                       </el-option>
                      </el-select>
    		    </el-form-item>
    		    <el-form-item label="状态">
    				<el-select v-model="searchForm.model.state" clearable>
                        <el-option v-for="option in stateOptions" :label="option.label" :value="option.value" :key="option.value">
                       </el-option>
                      </el-select>
    		    </el-form-item>
		  	</template>
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
		  	<el-table-column label="商品" width="80px">
            	<template v-if="scope.row.first_image" slot-scope="scope">
            	<el-image class="girdview-cell-img" :src="scope.row.first_image" fit="cover"></el-image>
            	</template>
            </el-table-column>
            <el-table-column prop="name" min-width="30%"></el-table-column>
            <el-table-column prop="state.label" label="状态" min-width="30%">
            	<template slot-scope="scope">
            		<el-tag v-if="scope.row.state.value == <?=Goods::STATE_DISABLE ?>" type="danger">{{scope.row.state.label}}</el-tag>
            		<el-tag v-else-if="scope.row.state.value == <?=Goods::STATE_ENABLE ?>" type="success">{{scope.row.state.label}}</el-tag>
            	</template>
            </el-table-column>
            <el-table-column prop="created_time" label="创建时间" min-width="30%"></el-table-column>
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
			initSearchFormUrl:'<?=Url::toRoute('search-form-data') ?>',
			deleteLoading:false,
			searchForm:{
				default:function(){
					return {
						'keyword':'',
						'state':'',
						'category_id':'',
						'tag_id':''
					};
				}
			},
			listRequestParams:{
				default:function(){
					return {
						type:'<?=Goods::TYPE_DP ?>'
					};
				}
			},
			selectRows:[],
			tabOptions:<?=HtmlHelper::encodeMapOptions(Goods::$typeMap) ?>,
			stateOptions:<?=HtmlHelper::encodeMapOptions(Goods::$stateMap) ?>,
			categoryNodes:[],
			tagOptions:[]
        }
		data.searchForm.model = data.searchForm.default();
		data.listRequestParams.model = data.listRequestParams.default();
		return data;
	},
	mounted:function(){
		var _this = this;
		$util.request($util.url.stringify(this.initSearchFormUrl,{type:this.listRequestParams.model.type}),{
			success:function(response){
				_this.categoryNodes = response.data.categoryNodes;
				_this.tagOptions = response.data.tags;
			}
		});
	},
	methods:{
		onCreate:function(){
			location.href = $util.url.stringify(this.createUrl,{type:this.listRequestParams.model.type});
		},
		onEdit:function(row){
			location.href = $util.url.stringify(this.editUrl,{id:row.id});
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
		onChangeTab:function(tab){
			this.$refs['dataGirdview'].setRequestParams({
				'type':tab.name,
				'category_id':''
			});
		}
	}
});
</script>