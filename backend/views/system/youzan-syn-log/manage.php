<?php
use common\librarys\Url;
use app\models\SystemUser;

$this->title = "同步日志";
$frameConfigs = [
    'paths'=>[
        '系统管理',
        $this->title
    ],
    'activeMenu'=>[1,1]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
		  <data-girdview row-key="id" ref="dataGirdview" :url="listUrl" :search-form="searchForm" :hash-query="true">
            <el-table-column prop="api_name" label="接口" min-width="18%"></el-table-column>
            <el-table-column prop="created_time" label="调用时间" min-width="15%"></el-table-column>
            <el-table-column prop="syn_state" label="同步状态" min-width="15%"></el-table-column>
            <el-table-column prop="response_content" label="响应" min-width="58%"></el-table-column>
            
		  </data-girdview>
		<?=$this->endContent() ?>
	</div>
</body>
<script>
Vue.$page.mixin.push({
	data:function(){
		var data = {
			listUrl:'<?=Yii::$app->user->checkAccess('list-data')?Url::toRoute('list-data'):'' ?>',
            reStartUrl:'<?=Url::toRoute(['restart']) ?>',
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
        onRestart:function(){
            location.href = $util.url.stringify(this.reStartUrl);
        }
	}
});
</script>