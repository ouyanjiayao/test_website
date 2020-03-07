<?php
use common\librarys\Url;

$this->title = "订单管理";
$frameConfigs = [
    'paths'=>[
        $this->title
    ],
    'activeMenu'=>[3]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
		  <data-girdview row-key="id" ref="dataGirdview" :url="listUrl" :search-form="searchForm" :hash-query="true">
		  	<template slot="searchForm">
                <el-form-item label="关键词">
    				<el-input v-model="searchForm.model.keyword"></el-input>
    		    </el-form-item>
		  	</template>
		  	<el-table-column prop="id" label="ID" min-width="10%"></el-table-column>
            <el-table-column prop="tid" label="有赞订单号" min-width="30%"></el-table-column>
            <el-table-column prop="order_num" label="单号" min-width="15%"></el-table-column>
            <el-table-column prop="order_state" label="状态" min-width="15%"></el-table-column>
            <el-table-column prop="zt_print_state" label="总台打印时间" min-width="15%"></el-table-column>
            <el-table-column prop="fk_print_state" label="分控打印时间" min-width="15%"></el-table-column>
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
			editUrl:'<?=Yii::$app->user->checkAccess('edit')?Url::toRoute('edit'):'' ?>',
			searchForm:{
				default:function(){
					return {
						'keyword':''
					};
				}
			}
        }
		data.searchForm.model = data.searchForm.default();
		return data;
	},
	methods:{
		onEdit:function(row){
			location.href = $util.url.stringify(this.editUrl,{id:row.id});
		}
	}
});
</script>