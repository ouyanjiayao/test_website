<?php
use common\librarys\Url;
use common\librarys\HtmlHelper;

$this->title = "图片管理";
$frameConfigs = [
    'paths'=>[
        '上传管理',
        $this->title
    ],
    'activeMenu'=>[4,1]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<div class="manage-left-nav">
				<div class="manage-left-nav-tree-wrap">
					<div class="manage-left-nav-tree-title">文件夹</div>
					<el-tree ref="leftTree" :current-node-key="listRequestParams.model.node_id" class="manage-left-nav-tree" :data="treeNodes" node-key="value" :expand-on-click-node="false" highlight-current @node-click="onSelectTreeNode"></el-tree>
				</div>
			</div>
			<div class="manage-right-content">
				<image-girdview :select-rows.sync="selectRows" row-key="id" ref="imageGirdview" :url="listUrl" :hash-query="true" :request-params="listRequestParams" :each-row="eachRow">
					<template slot="rowToolbar" slot-scope="scope">
							<el-popover v-model="scope.row['_setNameForm']['show']" placement="bottom" title="改名" width="220"
                            trigger="click" @show="scope.row['_setNameForm']['data']['name'] = scope.row['name']">
                            <el-button slot="reference" type="text">改名</el-button>
                            <el-form>
                        		<el-form-item>
                					<el-input size="mini" v-model="scope.row['_setNameForm']['data']['name']"></el-input>
                        	  </el-form-item>
                    		</el-form>
                            <div class="pop-button-wrap">
                            	<el-button size="mini" type="primary" native-type="submit" @click="onSubmitPropForm('_setNameForm',scope.row)" :loading="scope.row['_setNameForm']['loading']">确定</el-button>
                            	<el-button size="mini" @click="scope.row['_setNameForm']['show'] = false">取消</el-button>
                            </div>
                          </el-popover>
                          <el-popover v-model="scope.row['_setDirForm']['show']" title="移动" placement="bottom" width="220" trigger="click" @show="scope.row['_setDirForm']['data']['dir_id'] = scope.row['dir_id']">
                            <el-button slot="reference" type="text">移动</el-button>
                            <el-cascader v-model="scope.row['_setDirForm']['data']['dir_id']" style="width:100%;" size="mini" :clearable=true :options="dirs" :props="{ expandTrigger: 'click',emitPath:false,checkStrictly:true }"></el-cascader>
                            <div class="pop-button-wrap">
                            	<el-button size="mini" type="primary" native-type="submit" @click="onSubmitPropForm('_setDirForm',scope.row)" :loading="scope.row['_setDirForm']['loading']">确定</el-button>
                            	<el-button size="mini" @click="scope.row['_setDirForm']['show'] = false">取消</el-button>
                            </div>
                          </el-popover>
                          <el-button slot="reference" type="text" @click="onOpenLink(scope.row)">链接</el-button>
					</template>
					<template slot="toolbar">
        		  		<el-button-group>
                    		<el-button :disabled="!uploadUrl || !uploadRecordUrl" size="small" type="primary" @click="onCreate">上传</el-button>
                            <el-button :disabled="!deleteUrl || selectRows.length<=0" size="small" type="danger" @click="onDelete" :loading="deleteLoading">删除</el-button>
        				</el-button-group>
        		  	</template>
				</image-girdview>
		  	</div>
		<?=$this->endContent() ?>
		<el-dialog title="上传" :visible.sync="showUploadDialog" width="900px" custom-class="form-dialog" @open="showUpForm=true" @closed="showUpForm=false">
          <div class="form-dialog-wrap">
			<image-upload-form v-if="showUpForm" :upload-url="uploadUrl" :record-url="uploadRecordUrl" :dirs="dirs" :dir-id="listRequestParams.model.node_id" :on-submit="onUploadSubmit"></image-upload-form>
		  </div>
        </el-dialog>
	</div>
</body>
<script>
Vue.$page.mixin.push({
	data:function(){
		var data = {
			listUrl:'<?=Yii::$app->user->checkAccess('list-data')?Url::toRoute('list-data'):'' ?>',
			deleteUrl:'<?=Yii::$app->user->checkAccess('delete')?Url::toRoute('delete'):'' ?>',
			uploadUrl:'<?=Yii::$app->user->checkAccess('upload')?Url::toRoute('upload'):'' ?>',
			uploadRecordUrl:'<?=Yii::$app->user->checkAccess('record')?Url::toRoute('record'):'' ?>',
			deleteLoading:false,
			treeNodes: <?=HtmlHelper::encodeJson($dirs) ?>,
			dirs:[],
			listRequestParams:{
				default:function(){
					return {
						node_id:''
					};
				}
			},
			showUploadDialog:false,
			showUpForm:false,
			selectRows:[]
        }
		Object.assign(data.dirs,data.treeNodes);
		data.treeNodes.splice(0,0,{value:'',label:'全部'});
		data.listRequestParams.model = data.listRequestParams.default();
		return data;
	},
	mounted:function(){
		this.$refs['leftTree'].setCurrentKey(this.listRequestParams.model.node_id);
	},
	methods:{
		onCreate:function(){
			this.showUploadDialog = true;
		},
		onDelete:function(){
			var _this = this;
			this.$refs['imageGirdview'].submit(this.deleteUrl,{
				confirm:'确认删除所选记录？',
				before:function(){
					_this.deleteLoading = true;
				},
				complete:function(response){
					_this.deleteLoading = false;
				}
			});
		},
		eachRow:function(row){
			row['_setNameForm'] = {
				show:false,
				data:{
					id:row['id'],
					name:row['name'],
					set_type:1
				},
				loading:false
			};
			row['_setDirForm'] = {
				show:false,
				data:{
					id:row['id'],
					dir_id:row['dir_id'],
					set_type:2
				},
				loading:false
			};
			return row;
		},
		onSelectTreeNode:function(node){
			this.$refs['imageGirdview'].setRequestParams({
				'node_id':node.value
			});
		},
		onUploadSubmit:function(list){
			this.showUploadDialog = false;
			this.$refs['imageGirdview'].changePage(1);
		},
		onSubmitPropForm:function(form,row){
			var _this = this;
			row[form]['loading'] = true;
			$util.request('<?=Url::toRoute('set') ?>',{
				method:'post',
				data:row[form]['data'],
				success:function(responce){
					Vue.prototype.$notify({
	      				  type: "success",
	      		          title: '提示',
	      		          message: '修改成功',
	      		          position: 'bottom-right'
	      		    });
					row[form]['show'] = false;
					_this.$refs['imageGirdview'].loadData();
				},
				complete:function(){
					row[form]['loading'] = false;
				}
			});
		},
		onOpenLink:function(row){
			if(row['src_url'])
			{
				window.open(row.src_url);
			}
		}
	}
});
</script>