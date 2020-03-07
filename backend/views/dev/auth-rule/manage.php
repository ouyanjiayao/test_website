<?php
use common\librarys\Url;

$this->title = "权限规则";
$frameConfigs = [];
?>
<style>
.girdview-container{
	padding: 20px;
	min-height: 400px;
}

.girdview-search-wrap{
	padding-top: 18px;
    padding-left: 18px;
	background-color: #f2f2f2;
	margin-bottom: 20px;
}
</style>

<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
		  <div class="girdview-container">
		      <div class="girdview-search-wrap">
		          <el-form size="small" :inline="true" ref="searchForm" :model="searchForm" label-width="auto" @submit.native.prevent >
		                <el-form-item label="关键词">
            				<el-input v-model="searchForm.keyword"></el-input>
            		    </el-form-item>
    					<el-form-item>
    					   <el-button size="small" type="primary" icon="el-icon-search" native-type="submit">搜索</el-button>
    					 </el-form-item>
    				</el-form>
		      </div>
		   <el-table
                :data="tableData"
                style="width: 100%;margin-bottom: 20px;"
                row-key="id">
                <el-table-column
                  type="selection">
                </el-table-column>
                <el-table-column
                  prop="name"
                  label="姓名"
                  min-width="35%">
                </el-table-column>
                <el-table-column
                  prop="date"
                  label="日期"
                  min-width="35%">
                </el-table-column>
                <el-table-column
                  prop="address"
                  label="地址">
                </el-table-column>
              </el-table>
		  </div>
		<?=$this->endContent() ?>
	</div>
</body>
<script>
Vue.$page.mixin.push({
	data:function(){
		var data = {
				 expands: ['name'],
				searchForm:{},
	        tableData: [{
	            id: 1,
	            date: '2016-05-02',
	            name: '王小虎',
	            address: '上海市普陀区金沙江路 1518 弄'
	          }, {
	            id: 2,
	            date: '2016-05-04',
	            name: '王小虎',
	            address: '上海市普陀区金沙江路 1517 弄'
	          }, {
	            id: 3,
	            date: '2016-05-01',
	            name: '王小虎',
	            address: '上海市普陀区金沙江路 1519 弄',
	            children: [{
	                id: 31,
	                date: '2016-05-01',
	                name: '王小虎',
	                address: '上海市普陀区金沙江路 1519 弄'
	              }, {
	                id: 32,
	                date: '2016-05-01',
	                name: '王小虎',
	                address: '上海市普陀区金沙江路 1519 弄',
	                children: [{
		                id: 34,
		                date: '2016-05-01',
		                name: '王小虎',
		                address: '上海市普陀区金沙江路 1519 弄'
		              }, {
		                id: 35,
		                date: '2016-05-01',
		                name: '王小虎',
		                address: '上海市普陀区金沙江路 1519 弄',
		                children: [{
			                id: 36,
			                date: '2016-05-01',
			                name: '王小虎',
			                address: '上海市普陀区金沙江路 1519 弄'
			              }, {
			                id: 37,
			                date: '2016-05-01',
			                name: '王小虎',
			                address: '上海市普陀区金沙江路 1519 弄'
			            }]
		            }]
	            }]
	          }, {
	            id: 4,
	            date: '2016-05-03',
	            name: '王小虎',
	            address: '上海市普陀区金沙江路 1516 弄'
	          }]
        }
		return data;
	}
});
</script>