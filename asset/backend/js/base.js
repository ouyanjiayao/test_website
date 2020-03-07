Vue.component('data-girdview',$com_dataGirdview={
	props:{
		'url':{},
		'isTree':{},
		'eachRow':{},
		'selectLimit':{},
		'reserveSelection':{
			default:function(){
				return false;
			}
		},
		'requestParams':{
			default:function(){
				return {
					model:{},
					default:function(){
						return {};
					}
				};
			}
		},
		'searchForm':{
			default:function(){
				return {
					model:{},
					default:function(){
						return {};
					}
				};
			}
		},
		'pageSize':{
			default:function(){
				return $baseConfig.table.pageSize;
			}
		},
		'hashQuery':{
			default:function(){
				return false;
			}
		},
		'loading':{
			default:function(){
				return false;
			}
		}
	},
	template:`
		<div class="girdview-container">
				<div ref="scopeViewBlock"></div>
				<div class="girdview-main">
            	      <div v-if="$slots['searchForm']" class="girdview-search-wrap">
            	          <el-form size="small" :inline="true" ref="searchForm" :model="searchForm?searchForm['model']:null" label-width="auto" @submit.native.prevent >
                				<slot name="searchForm"></slot>
            					<el-form-item>
            					   <el-button size="small" type="primary" native-type="submit" @click="onSearch" :disabled="loading">搜索</el-button>
            					 </el-form-item>
            				</el-form>
            	      </div>
            	      <slot name="headerContent"></slot>
	      		      <div class="girdview-table-wrap" v-loading="loading">
	      		      		<el-table ref="elTable" :tree-props="{ children: '_children' }" @select="onSelect" @select-all="onSelectAll" class="girdview-table" :data="tableData" v-bind="$attrs" v-on="$listeners">
                        		<slot></slot>
                            </el-table>
	      			 </div>
    	      </div>
	      	  <div class="girdview-toolbar">
  	  				<div class="girdview-toolbar-left">
						<slot name="toolbar"></slot>
					</div>
					<div class="girdview-toolbar-right">
                		<el-pagination ref="pagination" :current-page.sync="showPageNum" @current-change="changePage" :disabled="loading" class="girdview-pagination" :layout="isTree?'total':'total,prev, pager, next,jumper'" :total="total" :page-size="isTree?null:pageSize">
                		</el-pagination>
						<div class="girdview-pagination-reload"><el-button :loading="loading" size="small" icon="el-icon-refresh-right" type="primary" circle @click="onReload"></el-button></div>
					</div>
				</div>
    	  </div>
	`,
	data:function(){
		var data = {
			 total:0,
			 pageNum:1,
			 showPageNum:1,
			 tableData: [],
			 hashParams:null,
			 searchParams:{},
			 reqVersion:0,
			 selectRows:[],
			 checkAll:{
	    		value:false,
	    		indeterminate:false
	    	}
		};
		return data;
	},
	mounted:function(){
		var _this = this;
		if(this.searchForm.default){
			this.searchForm.model = this.searchForm.default();
		}
		if(this.requestParams.default){
			this.requestParams.model = this.requestParams.default();
		}
		if(this.hashQuery){
			this.initHashParams();
			window.onhashchange = function(){
				_this.initHashParams();
				_this.loadData();
			};
		}
		this.loadData();
	},
	methods:{
		setHashParams:function(params){
			var srcHash =  location.hash;
			$util.url.setHashParams(params);
			if(srcHash == location.hash)
			{
				this.loadData();
			}
		},
		initHashParams:function(){
			if(this.searchForm.default){
				this.searchForm.model = this.searchForm.default();
			}
			if(this.requestParams.default){
				this.requestParams.model = this.requestParams.default();
			}
			var hashParams = $util.url.getHashParams();
			var pageNum = 1;
			if(hashParams)
			{
				for(var key in this.searchForm.model)
				{
					if(hashParams.hasOwnProperty(key))
					{
						this.searchForm.model[key] = hashParams[key];
					}
				}
				for(var key in this.requestParams.model)
				{
					if(hashParams.hasOwnProperty(key))
					{
						this.requestParams.model[key] = hashParams[key];
					}
				}
				if(hashParams['page'])
				{
					pageNum = parseInt(hashParams['page']);
				}
			}
			this.pageNum = pageNum;
			this.hashParams = hashParams?hashParams:{};
		},
		onReload:function(){
			this.loadData();
		},
		onSearch:function(){
			this.pageNum = 1;
			if(this.hashQuery)
			{
				if(this.hashParams['page'])
				{
					this.hashParams['page'] = 1;
				}
				Object.assign(this.hashParams,this.searchForm.model);
				this.setHashParams(this.hashParams);
			}else{
				this.loadData();
			}
		},
		setRequestParams:function(params){
			this.pageNum = 1;
			for(var key in params)
			{
				this.requestParams.model[key] = params[key];
			}
			if(this.hashQuery)
			{
				if(this.hashParams['page'])
				{
					this.hashParams['page'] = 1;
				}
				Object.assign(this.hashParams,this.requestParams.model);
				this.setHashParams(this.hashParams);
			}else{
				this.loadData();
			}
		},
		loadData:function(){
			if(!this.url)
			{
				return;
			}
			this.reqVersion++;
			var currReqVersion = this.reqVersion;
			var _this = this;
			this.loading = true;
			var data = Object.assign({},this.hashParams,this.searchForm.model,this.requestParams.model,this.isTree?null:{
				page:this.pageNum,
				size:this.pageSize
			})
			$util.request(this.url,{
				params:data,
				success:function(response)
				{
					if(currReqVersion < _this.reqVersion)
					{
						return;
					}
					var data = null;
					var total = 0;
					if(_this.isTree)
					{
						data = $util.generateTreeList(response.data);
						total = data.length;
					}else{
						data = response.data.rows;
						total = response.data.total;
					}
					if(_this.eachRow)
					{
						for(var i=0;i<data.length;i++)
						{
							data[i] = _this.eachRow(data[i]);
						}
					}
					_this.setTableData (data);
					_this.total = total;
					_this.$emit('update:table-data', data);
					if(!_this.reserveSelection)
					{
						_this.selectRows = [];
						_this.$emit('update:select-rows', _this.selectRows);
					}else{
						_this.$nextTick(function(){
							for(var i=0;i<_this.selectRows.length;i++)
							{
								var existsRow = _this.tableData.find(function(item){
									return item[_this.$attrs['row-key']] == _this.selectRows[i][_this.$attrs['row-key']];
								});
								if(existsRow && _this.$refs.elTable)
									_this.$refs.elTable.toggleRowSelection(existsRow,true);
							}
						});
					}
					_this.$refs['scopeViewBlock'].scrollIntoView({block: "end", inline: "nearest",behavior:"smooth"});
				},
				complete:function(){
					if(currReqVersion < _this.reqVersion)
					{
						return;
					}
					_this.showPageNum = _this.pageNum;
					_this.loading = false;
				}
			});
		},
		changePage:function(page){
			this.pageNum = page;
			if(this.hashQuery)
			{
				this.hashParams['page'] = page;
				Object.assign(this.hashParams,this.requestParams.model);
				this.setHashParams(this.hashParams);
			}else{
				this.loadData();
			}
		},
		getSelectRows:function(colunm){
			var result = [];
			if(!colunm)
			{
				result = this.selectRows;
			}else{
				for(var i=0;i<this.selectRows.length;i++)
				{
					result.push(this.selectRows[i][colunm]);
				}
			}
			if(result.length<=0)
			{
				return false;
			}
			return result;
		},
		submit:function(url,options){
			var _this = this;
			options = Object.assign({},{
				colunm:this.$attrs['row-key'],
				confirm:false
			},options);	
			var rowDatas = this.getSelectRows(options.colunm,true);
			if(rowDatas)
			{
				var request = function(){
					if(options.before)
					{
						options.before();
					}
					var data = {};
					data[options.colunm] = rowDatas;
					$util.request(url,{
						data:data,
						method:'post',
						success:function(response){	
							var type = 'error';
							if(response.data.success)
							{
								type = 'success';
								_this.loadData();
							}
							_this.$notify({
								type:type,
					          	title: '提示',
						          message: response.data.message,
						          position: 'bottom-right'
					        });
					        if(options.success)
					        {
					        	options.success(response);
						    }
						},
						error:function(response){
							if(options.error)
					        {
					        	options.error(response);
						    }
						},
						complete:function(response){
							if(_this.reserveSelection)
							{
								_this.selectRows = [];
								_this.$emit('update:select-rows', _this.selectRows);
							}
							if(options.complete)
					        {
					        	options.complete(response);
						    }
						}
					});
				};
				if(options.confirm)
				{
					$util.confirm(options.confirm,{
						type:'warning',
						confirmCallback:function(){
							request();
						}
					});
				}else{
					request();
				}
			}
		},
		setTableData:function(tableData){
			var _this = this;
			if(!this.$refs['elTable'])
    		{
	    		for(var i=0;i<tableData.length;i++)
	    		{
	    			var check = false;
	    			if(this.reserveSelection)
					{
	    				var index = this.selectRows.findIndex(function(item){
	    					return item[_this.$attrs['row-key']] == tableData[i][_this.$attrs['row-key']];
	    				});
	    				if(index >= 0)
	    				{
	    					check = true;
	    				}
					}
	    			tableData[i]['_check'] = check;
	    		}
    		}
			this.tableData = tableData;
			if(!this.$refs['elTable'])
			{
				this.setCheckAll();
			}
		},
		onSelect:function(rows,row){
			var _this = this;
			var selectRows = this.selectRows;
			if(this.reserveSelection)
			{
				var existsIndex = selectRows.findIndex(function(item){
					return item[_this.$attrs['row-key']] == row[_this.$attrs['row-key']];
				});
				var checkRow = rows.find(function(item){
					return item[_this.$attrs['row-key']] == row[_this.$attrs['row-key']];
				});
				if(checkRow)
				{
					if(existsIndex<0)
					{
						selectRows.push(checkRow);
					}
				}else{
					if(existsIndex>=0)
						selectRows.splice(existsIndex,1);
				}
			}else{
				selectRows = rows;
			}
			this.selectRows = selectRows;
			this.$emit('update:select-rows',this.selectRows);
		},
		onSelectAll:function(rows){
			var _this = this;
			var selectRows = this.selectRows;
			if(this.reserveSelection)
			{
				var check = rows.length>0?true:false;
				for(var i=0;i<this.tableData.length;i++)
				{
					var row = this.tableData[i];
					var existsIndex = selectRows.findIndex(function(item){
						return item[_this.$attrs['row-key']] == row[_this.$attrs['row-key']];
					});
					if(check){
						if(existsIndex<0)
							selectRows.push(row);
					}else{
						if(existsIndex>=0)
							selectRows.splice(existsIndex,1);
					}
				}
			}else{
				selectRows = rows;
			}
			this.selectRows = selectRows;
			this.$emit('update:select-rows',this.selectRows);
		},
		onChangeCheck:function(value,row){
			if(value)
			{
				if(this.selectLimit && this.selectRows.length>=this.selectLimit)
				{
					row['_check'] = false;
					Vue.prototype.$notify({
						  type: "error",
				          title: '提示',
				          message: '最多只能选择'+this.selectLimit+'张图片',
				          position: 'bottom-right'
				    });
					return;
				}
			}
			this.setSelectRows(value,row);
    		this.setCheckAll();
    		this.$emit('update:select-rows', this.selectRows);
    	},
    	onChangeCheckAll:function(value){
    		if(this.checkAll.indeterminate)
    		{
    			this.checkAll.indeterminate = false;
    		}
    		for(var i=0;i<this.tableData.length;i++)
    		{
    			if(value)
    			{
    				if(this.selectLimit && this.selectRows.length>=this.selectLimit)
    				{
    					break;
    				}
    			}
    			this.tableData[i]['_check'] = value;
    			this.setSelectRows(value,this.tableData[i]);
    		}
    		this.$emit('update:select-rows', this.selectRows);
    		this.setCheckAll();
    	},
    	setSelectRows:function(check,row){
    		var _this = this;
    		var index = this.selectRows.findIndex(function(item){
				return item[_this.$attrs['row-key']] == row[_this.$attrs['row-key']];
			});
    		if(check)
			{
    			if(index<0)
    			{
    				this.selectRows.push(row);
    			}
			}else{
				if(index>=0)
					this.selectRows.splice(index,1)
			}
    	},
    	setCheckAll:function(){
    		var checkCount = 0;
			var indeterminate = false;
			var checkAll = false;
			for(var i=0;i<this.tableData.length;i++)
			{
				if(this.tableData[i]['_check'])
				{
					checkCount++;
				}
			}
			if(this.tableData.length > 0 && checkCount >= this.tableData.length)
			{
				checkAll = true;
			}else if(checkCount>0){
				indeterminate = true;
			}
			this.checkAll.indeterminate = indeterminate;
			this.checkAll.value = checkAll;
    	}
	}
});

Vue.component('image-girdview',{
	props:{
		showCheckAll:{
			default:function(){
				return true;
			}
		}
	},
	template:`<div class="girdview-container">
	<div ref="scopeViewBlock"></div>
					<div class="girdview-main">
						<slot name="headerContent"></slot>
			      <div class="girdview-table-wrap image-girdview-table-wrap" v-loading="loading">
			      		<div v-show="tableData.length<=0">
    		 				<div class="el-table__empty-block"><span class="el-table__empty-text">暂无数据</span></div>
    		 			</div>
    		 			<div v-show="showCheckAll && tableData.length>0" class="girdview-checkall-wrap"><el-checkbox @change="onChangeCheckAll" v-model="checkAll.value" :indeterminate="checkAll.indeterminate">全选</el-checkbox></div>
    		 			<div v-for="(row,index) in tableData" :key="row[$attrs['row-key']]" class="image-girdview-row" :body-style="{ padding: '0px' }">
              				<div  @click="onClickImage(index,row)"><el-image lazy class="image-girdview-row-wrap" :src="row['thumb_url']" fit="contain"></el-image></div>
                            <div class="image-girdview-row-name"><el-checkbox @change="value=>onChangeCheck(value,row)" v-model="row['_check']" class="image-girdview-row-checkbox">{{row['name']}}</el-checkbox></div>
                            <slot name="rowToolbar" :row="row"></slot>
                        </div>
                		<div class="clear"></div>
				 </div>
			</div>
			<div class="girdview-toolbar">
					<div class="girdview-toolbar-left">
					<slot name="toolbar"></slot>
				</div>
				<div class="girdview-toolbar-right">
					<el-pagination ref="pagination" :current-page.sync="showPageNum" @current-change="changePage" :disabled="loading" class="girdview-pagination" :layout="isTree?'total':'total,prev, pager, next,jumper'" :total="total" :page-size="isTree?null:pageSize">
					</el-pagination>
					<div class="girdview-pagination-reload"><el-button :loading="loading" size="small" icon="el-icon-refresh-right" type="primary" circle @click="onReload"></el-button></div>
				</div>
			</div>
			</div>`,
	mixins:[$com_dataGirdview],
	methods:{
		onClickImage:function(index,row)
		{
			row['_check'] = !row['_check'];
			this.onChangeCheck(row['_check'],row);
		}
	}
});

Vue.component('tree-expand-column', {
	props:['prop'],
	template:`<el-table-column v-bind="$attrs" :prop="prop">
    		<template slot-scope="scope">
				<div :style="{'padding-left':(scope.row._nodeLevel*32) + 'px' }"><i v-if="scope.row.children && scope.row.children.length>0" class="el-icon-caret-bottom tree-girdview-node-icon"></i><span>{{scope.row[prop]}}</span></div>
        	</template>
        </el-table-column>`
});

Vue.component('form-content', {
	props:{
		'labelWidth':{
			default:function(){
				return 'auto';
			}
		}
	},
	watch:{
		requestInitDataLoading:function(){
			this.$emit('update:request-init-loading', this.requestInitDataLoading);
		}
	},
	template:`
	<el-form size="small" ref="form" :label-width="labelWidth" v-bind="$attrs" v-on="$listeners" @submit.native.prevent>
	 <transition name="el-fade-in-linear">
	<el-alert v-if="errorMessage" class="form-alert" :title="errorMessage" type="error" effect="dark" show-icon @close="errorMessage = ''">
	 </transition>
  </el-alert>
        <slot></slot>
    	<div v-if="requestInitDataError || $slots['toolbar']" class="form-toolbar">
    		<template v-if="requestInitDataLoading">
				<el-button icon="el-icon-loading" type="text" disabled>加载中</el-button>
			</template>
			<template v-else>
        		<slot v-if="!requestInitDataError" name="toolbar"></slot>
        		<span class="form-error-tip" v-else>错误：表单数据加载失败，请刷新后重试</span>
			</template>
      	</div>
    </el-form>`,
   	data:function(){
   	   	var data = {
   	   		requestInitDataLoading:false,
   	   		requestInitDataError:false,
 	   	   	errorMessage:''
 	   	};
		return data;
   	},
    methods:{
		submit:function(url,options){
			var _this = this;
			options = Object.assign({
				showMessage:true,
				method:'post'
			},options);
			this.$refs.form.validate(function(valid){
				if(!valid)
				{
					return;
				}
				var data = Object.assign({},_this.$attrs.model,options.data);
				if(options.before)
				{
					options.before(data);
				}
				$util.request(url,{
					method:options.method,
					data:data,
					success:function(response)
					{
						if(response.data.success)
						{
							_this.errorMessage = '';
						}
						if(options.success)
						{
							options.success(response);
						}
						if(options.showMessage){
							if(!response.data.success)
							{
								_this.errorMessage = response.data.message;
							}else{
								_this.$alert(response.data.message, '提示', {
									type: 'success',
									showClose:false,
						            callback: function(action){
						            	setTimeout(function(){
							            	location.reload();
						            	},250);
						            }
						        });
							}
						}
					},
					error:function(response)
					{
						if(options.error)
						{
							options.error(response);
						}
					},
					complete:function(response){
						if(options.complete)
						{
							options.complete(response);
						}
					}
				});
			})
		},
		setErrorMessage:function(message){
			this.errorMessage = message;
		},
		requestInitData:function(url,options){
			var _this = this;
			options = options || {};
			_this.requestInitDataLoading = true;
			$util.request(url,{
				showErrorMessage:false,
				success:function(response){
					if(options.success)
					{
						options.success(response);
					}
				},
				error:function(response){
					if(options.error)
					{
						options.error(response);
					}
					_this.requestInitDataError = true;
				},
				complete:function(response){
					if(options.complete)
					{
						options.complete(response);
					}
					_this.requestInitDataLoading = false;
				}
			});
		}
    }
});

Vue.component('image-upload-box',{
	props:{
		limit:{},
		value:{},
		size:{},
		dirId:{},
		loading:{},
		uploadFileName:{},
		imageListUrl:{
			default:function(){
				return $baseConfig.imageUpload.imageListUrl;
			}
		},
		dirOptionsUrl:{
    		default:function(){
    			return $baseConfig.imageUpload.dirOptionsUrl;
    		}
		},
		uploadUrl:{
    		default:function(){
    			return $baseConfig.imageUpload.uploadUrl;
    		}
		},
		uploadRecordUrl:{
    		default:function(){
    			return $baseConfig.imageUpload.uploadRecordUrl;
    		}
		}
	},
	model:{
		prop: 'value',
	    event: 'changeValue'
	},
	template:`<div class="image-upload-box-list">
		<div v-if="imageList.length>0" v-for="(item,index) in imageList" :key="item.id" class="image-upform-row-wrap">
    		<div @mouseover="onItemHover(item)" @mouseleave="onItemLeave(item)">
    			<a :href='item.src_url' target="_blank"><el-image class="image-upform-img" :src="item.thumb_url" fit="contain"/></a>
    			<transition name="el-fade-in-linear">
    				<span v-show="item.showRemove" class="image-upform-remove" @click="onItemRemove(index)"><i class="el-icon-close"></i></span>
    			</transition>
    		</div>
		</div>
		<transition name="el-fade-in-linear">
    		<div v-show="!limit || currLimit>0" class="image-upform-row-wrap image-upform-plus" v-loading="loading"><div class="image-upform-plus-content" @click="onShowDialog">+</div></div>
    	</transition>
		<div class="clear"></div>
    	<div v-if="uploadTip" class="el-upload__tip">{{uploadTip}}</div>
		<el-dialog :title="title" :visible.sync="showDialog" width="1040px" custom-class="form-dialog" :append-to-body="true" @open="showGirdview=true" @closed="onCloseDialog">
			<div slot="title" class="upselect-image-dialog-title">{{title}}<el-button v-show="uploadWindow" class="upselect-image-dialog-back" icon="el-icon-arrow-left" size="medium" type="text" @click="uploadWindow=false">返回</el-button></div>
			<div class="form-dialog-wrap">
				<div v-show="!uploadWindow">
            		<div class="manage-left-nav">
                		<div class="manage-left-nav-tree-wrap">
                			<div class="manage-left-nav-tree-title">文件夹</div>
                			<el-tree ref="leftTree" :default-expanded-keys="test" class="manage-left-nav-tree" :data="treeNodes" node-key="value" :expand-on-click-node="false" highlight-current @node-click="onSelectTreeNode"></el-tree>
                		</div>
                	</div>
            		<div class="manage-right-content">
                		<image-girdview :select-limit="limit?currLimit:null" v-if="showGirdview" :reserve-selection="true" :reserve-selection="true" :select-rows.sync="selectRows" :show-check-all="false" row-key="id" ref="imageGirdview" :url="imageListUrl" :request-params.sync="listRequestParams">
    					<div slot="headerContent" class="upselect-image-girdview-header"><el-button type="primary" size="small" @click="uploadWindow = true;">上传图片</el-button></div>
                			<template slot="toolbar">
                		  		<el-button-group>
                            		<el-button type="success" size="small" :disabled="selectRows.length<=0" @click="onConfirmSelect">确定</el-button>
                				</el-button-group>
                		  	</template>
                		</image-girdview>
                  	</div>
				</div>
				<div v-if="uploadWindow">
					<image-upload-form :upload-file-name="uploadFileName" :limit="limit?currLimit:null" :upload-url="uploadUrl" :record-url="uploadRecordUrl" :dirs="dirs" :dir-id="listRequestParams.model.node_id" :on-submit="onUploadSubmit"></image-upload-form>
				</div>
    		</div>
      	</el-dialog>
	      </div>`,
	data:function(){
		var _this = this;
		var data = {
			test:[],
			showDialog:false,
			showGirdview:false,
			initDirs:false,
			treeNodes:[],
			dirs:[],
			listRequestParams:{
    			default:function(){
    				return {
    					node_id:_this.dirId?_this.dirId:''
    				};
    			}
    		},
    		selectRows:[],
			uploadWindow:false,
			imageList:[]
		};
		data.listRequestParams.model = data.listRequestParams.default();
		return data;
	},
	created:function(){
		if(this.value)
		{
			for(var key in this.value)
			{
				this.imageList.push({
					id:this.value[key]['id'],
					url:this.value[key]['url'],
					src_url:this.value[key]['src_url'],
					thumb_url:this.value[key]['thumb_url'],
					showRemove:false
				});
			}
			this.setValue();
		}
	},
	computed:{
		title:function(){
			if(this.uploadWindow)
			{
				return '上传图片';
			}else{
				return '选择图片';
			}
		},
		currLimit:function(){
			return this.limit - this.imageList.length;
		},
		uploadTip:function(){
 	 		var tips = [];
 	 		if(this.size)
 	 		{
 	 			tips.push('建议尺寸：'+this.size+'像素');
 	 	 	}
 	 		if(this.limit)
 	 		{
 	 			tips.push('最多上传'+this.limit+'张');
 	 	 	}
 	 	 	return tips.length<=0?false:tips.join('，');
	 	}
	},
	methods:{
		selectDirId:function(){
			var _this = this;
			this.$nextTick(function(){
				if(_this.dirId)
				{
					_this.$refs['leftTree'].setCurrentKey(_this.dirId);
					var expandNode = function(node){
						if(node['parent']){
							node['parent']['expanded'] = true;
							expandNode(node['parent']);
						}
					}
					expandNode(_this.$refs['leftTree'].getNode(_this.$refs['leftTree'].getCurrentNode()));
				}else{
					_this.$refs['leftTree'].setCurrentKey('');
				}
			});
		},
		onCloseDialog:function(){
			this.showGirdview = false;
			this.uploadWindow = false;
			this.selectRows = [];
		},
		onShowDialog:function(){
			this.$emit('show');
			var _this = this;
			if(!this.initDirs)
			{
				$util.request(this.dirOptionsUrl,{
					success:function(responce)
					{
						_this.initDirs = true;
						var data = responce.data;
						Object.assign(_this.dirs,data);
						data.splice(0,0,{value:'',label:'全部'});
						_this.treeNodes = data;
						_this.selectDirId();
					}
				});
			}else{
				this.selectDirId();
			}
			this.showDialog = true;
		},
		onSelectTreeNode:function(node){
			this.$refs['imageGirdview'].setRequestParams({
				'node_id':node.value
			});
		},
		onConfirmSelect:function(){
			this.done(this.selectRows);
		},
		onUploadSubmit:function(rows){
			this.done(rows);
		},
		done:function(rows){
			this.showDialog = false;
			for (var key in rows)
			{
				this.imageList.push({
					id:rows[key]['id'],
					url:rows[key]['url'],
					src_url:rows[key]['src_url'],
					thumb_url:rows[key]['thumb_url'],
					showRemove:false
				});
			}
			this.setValue();
		},
		onItemHover:function(item){
 	 	 	if(!item.done)
 	 	 	{
 	 	 		item.showRemove=true;
 	 	 	}
 	 	},
 	 	onItemLeave:function(item){
			item.showRemove=false;
	 	},
        onItemRemove:function(index){
			this.imageList.splice(index,1);
			this.setValue();
        },
        setValue:function(){
			var value = [];
			for(var key in this.imageList)
			{
				value.push({
					id:this.imageList[key]['id'],
					url:this.imageList[key]['url']
				});
			}
			this.$emit('changeValue', value);
        }
	}
});

Vue.component('image-upload-form', {
	props:{
		uploadFileName:{},
		onSubmit:{},
		uploadUrl:{
			default:function(){
				return $baseConfig.imageUpload.uploadUrl;
			}
		},
		recordUrl:{
			default:function(){
				return $baseConfig.imageUpload.uploadRecordUrl;
			}
		},
		limit:{},
		maxSize:{
			default:function(){
				return $baseConfig.imageUpload.maxSize;
			}
		},
		extName:{
			default:function(){
				return $baseConfig.imageUpload.extName;
			}
		},
		dirs:{
			default:function(){
				return [];
			}
		},
		dirId:{
			default:function(){
				return '';
			}
		}
	},
	template:`<form-content ref="form">
    	<div class="form-wrap">
    	<div class="form-item-wrap">
		<el-form-item label="文件夹">
		<el-cascader v-model="dirId"
     		:clearable=true
            :options="dirs"
            :props="{ expandTrigger: 'click',emitPath:false,checkStrictly:true }"
            ></el-cascader>
		</el-form-item>
    	 <el-form-item label="本地文件">
        	 <div>
          		<div v-if="list.length>0">
          			<div v-for="(item,index) in list" :key="item['id']" class="image-upform-row-wrap" v-loading="item.loading">
						<div @mouseover="onItemHover(item)" @mouseleave="onItemLeave(item)">
              				<el-image class="image-upform-img" :src="item.rawData" fit="contain"/>
							<transition name="el-fade-in-linear">
              				<span v-show="item.showRemove" class="image-upform-remove" @click="onItemRemove(index)"><i class="el-icon-close"></i></span>
							</transition>
							<transition name="el-fade-in-linear">
							<div v-show="item.error" class="image-upform-row-tip">上传失败</div>
							</transition>
						</div>
          			</div>
          		</div>
          		<el-upload :limit="limit" :on-exceed="onExceed" class="image-upform-plus-wrap" :action="uploadUrl" :before-upload="beforeUpload" ref="upload" :multiple="true" :show-file-list="false" :auto-upload="false" :on-change="onChange" accept="image/*">
		<transition slot="trigger" name="el-fade-in-linear"><div v-show="!loading && !allDone && isExceed" class="image-upform-row-wrap image-upform-plus">
            		<div class="image-upform-plus-content">+</div>
            	</div>
                </el-upload>
		</transition>
				<div class="clear"></div>
				<div v-if="uploadTip" class="el-upload__tip">{{uploadTip}}</div>
            </div>
    	 </el-form-item>
    	 </div>
    	</div>
    	<el-button-group slot="toolbar">
		<el-button v-if="allDone" size="small" type="primary" native-type="submit" @click="next()">确定</el-button>
		<el-button v-if="!allDone" size="small" type="primary" native-type="submit" @click="submit" :loading="loading" :disabled="disabledSubmit">开始上传</el-button>
        </el-button-group>
    </form-content>`,
	data:function(){
		return {
			list:[],
			loading:false,
			showInvaildText:false,
			uploading:[],
			allDone:false,
			allSuccess:false,
			successList:[],
			submitDirId:null
		};
 	},
 	computed:{
 		currLimit:function(){
			return this.limit - this.list.length;
		},
 		isExceed:function(){
 	 		if(this.limit &&  this.list.length>=this.limit)
 	 		{
 	 	 		return false;
 	 	 	}
			return true;
 	 	},
 		uploadTip:function(){
 	 		var tips = [];
 	 		if(this.extName)
 	 		{
 	 			tips.push('仅支持 '+this.extName.join('、')+'格式');
 	 	 	}
 	 		if(this.maxSize)
 	 		{
 	 			tips.push('大小不超过'+ (this.maxSize/1000).toFixed(1)+' MB');
 	 	 	}
 	 	 	return tips.length<=0?false:tips.join('，');
	 	},
		disabledSubmit:function(){
			var disabled = true;
			for(var i=0;i<this.list.length;i++)
            {
                if(this.list[i].file['status'] == 'ready')
                {
                	disabled = false;
                }
            }
            return disabled;
		}
	},
 	methods:{
 	 	onItemHover:function(item){
 	 	 	if(!item.done)
 	 	 	{
 	 	 		item.showRemove=true;
 	 	 	}
 	 	},
 	 	onItemLeave:function(item){
			item.showRemove=false;
	 	},
        onItemRemove:function(index){
			this.list.splice(index,1)
			this.$refs['upload'].uploadFiles.splice(index, 1);
        },
        onExceed:function(){
        	Vue.prototype.$notify({
				  type: "error",
		          title: '提示',
		          message: '最多只能选择'+this.currLimit+'张图片',
		          position: 'bottom-right'
		    });
        },
    	onChange:function(file, fileList){
        	var _this = this;
        	if(file.status =='ready')
        	{
            	var maxSizeError = false;
        		if(this.maxSize)
            	{
                	if(file.size>(this.maxSize*1000))
					{
                		maxSizeError = true;
					}
                }
                if(maxSizeError)
                {
                    for(var i=0;i<this.$refs['upload'].uploadFiles.length;i++)
                    {
                        if(file['uid'] == this.$refs['upload'].uploadFiles[i]['uid'])
                        {
                        	this.$refs['upload'].uploadFiles.splice(i, 1);
                        }
                    }
                    var message = this.uploadTip;
                	if(message && !this.showInvaildText)
    				{
    					this.showInvaildText = true;
                		Vue.prototype.$notify({
        					  type: "error",
        			          title: '提示',
        			          message: message,
        			          position: 'bottom-right',
        			          onClose:function(){
        			        	  _this.showInvaildText = false;
              			      }
        			    });
    				}
    				return;
                }
       		 	var reader = new FileReader();  
          		reader.onload = function(e) {  
          			_this.list.push({id:file['uid'],file:file,showRemove:false,error:false,rawData:this.result,loading:false,done:false,version:null});
          			file._listIndex = _this.list.length - 1;
        	    }  
        	    reader.readAsDataURL(file.raw); 
            }else if(file.status == 'success' || file.status =='fail')
            {
            	var listItem = this.list.find(function(item){
             	     return item.file.uid == file.uid;
             	 });
           	 	if(listItem)
           	 	{
               	 	listItem.loading = false;
                  	listItem.done = true;
                	if( file.status =='fail')
                	{
                		listItem.error = true;
                    }
               	}
				for(var i=0;i<this.uploading.length;i++)
				{
					if(this.uploading[i] == file['uid'])
					{
						this.uploading.splice(i,1);
						break;
					}
				}
				var allDone = this.uploading.length<=0?true:false;
                if(allDone)
                {	
                    var recordData = [];
                    var allSuccess = true;
                    for(var i=0;i<this.list.length;i++)
                    {
                        if(this.list[i]['error'])
                        {
                        	allSuccess = false;
                            break;
                        }else{
                        	response = this.list[i]['file']['response'];
                        	if(this.uploadFileName)
                        	{
                        		response['name'] = this.uploadFileName;
                        	}
                        	recordData.push(response);
                        }
                    }
                    if(recordData.length>0)
                    {
                       	 $util.request(this.recordUrl,{
      						method:'post',
      						data:{
      							dir_id:this.submitDirId,
								record:recordData
              				},
      						success:function(responce){
      		                    if(allSuccess)
      		                    {
      		                    	Vue.prototype.$notify({
      		            				  type: "success",
      		            		          title: '提示',
      		            		          message: '上传成功',
      		            		          position: 'bottom-right'
      		            		    });
      		            		    for(var i=0;i<responce.data.list.length;i++)
      		            		    {
          		            		    _this.successList.push(responce.data.list[i]);
          		            		}
      		                        _this.next();
      		                    }
      						},
      						complete:function(){
      		                    _this.loading = false;
      							_this.allSuccess = allSuccess;
        		                _this.allDone = allDone;
              				}
                         });
                    }else{
                    	this.loading = false;
                    	this.allSuccess = allSuccess;
                    	this.allDone = allDone;
                    }
                }
            }
        },
        beforeUpload:function(file){
        	 var listItem = this.list.find(function(item){
           	     return item.file.uid == file.uid;
           	 });
           	 if(listItem)
           	 {
            	listItem.loading = true;
             }
        },
        submit:function(){
            this.uploading = [];
            var loading = false;
        	for(var i=0;i<this.list.length;i++)
            {
                if(this.list[i].file['status'] == 'ready')
                {
                	loading = true;
                	this.uploading.push(this.list[i].file['uid'])
                }
            }
            this.loading = loading;
            this.submitDirId = this.dirId;
        	this.$refs['upload'].submit();
        },
        next:function(){
        	if(this.onSubmit)
                this.onSubmit(this.successList)
        }
    }
});

Vue.component('select-box',{
	props:{
		fastCreateUrl:{},
		fastCreateData:{},
		optionsUrl:{},
		optionsParams:{},
		options:{
			default:function(){
				return [];
			}
		}
	},
  	data:function(){
  		var data = {
			createValue:'',
			createLoading:false,
			initLoading:false,
			initOptions:false
		};
		return data;
    },
	template:`<el-select v-bind="$attrs" v-on="$listeners" @visible-change="onVisibleChange" popper-class="fast-create-select" :loading="initLoading">
		<div v-if="!initLoading" slot="empty" class="fast-create-select-wrap">
            <div class="fast-create-select-left">
            	<el-input v-model="createValue" :placeholder="$attrs['placeholder']" size="small"></el-input>
            </div>
            <div class="fast-create-select-right">
    			<el-button :disabled="createLoading || !createValue" size="small" @click="onFastCreate">新建</el-button>
            </div>
    	</div>
		<li class="fast-create-select-wrap">
            <div class="fast-create-select-left">
            	<el-input v-model="createValue" :placeholder="$attrs['placeholder']" size="small"></el-input>
            </div>
            <div class="fast-create-select-right">
    			<el-button :disabled="createLoading || !createValue" size="small" @click="onFastCreate">新建</el-button>
            </div>
    	</li>
			<el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value"></el-option>
          </el-select>`,
     methods:{
		onFastCreate:function(){
			var _this = this;
			if(this.fastCreateUrl)
			{
				this.createLoading = true;
				$util.request(this.fastCreateUrl,{
					method:'post',
					data:Object.assign({
						value:this.createValue
					},this.fastCreateData),
					success:function(responce){
						_this.onCreateOption(responce.data)
						_this.createValue = '';
					},
					complete:function(){
						_this.createLoading = false;
					}
				});
			}
		},
		onVisibleChange:function(value){
			var _this = this;
			if(value)
			{
				if(this.optionsUrl && !this.initOptions)
				{
					this.initLoading = true;
					$util.request(this.optionsUrl,{
						params:Object.assign({},this.optionsParams),
						success:function(responce){
							_this.options = responce.data;
							_this.$emit('update:options', _this.options);
							_this.initOptions = true;
						},
						complete:function(){
							_this.initLoading = false;
						}
					});
				}
				this.createValue='';
			}
		},
		onCreateOption:function(data){
			data.value = data.value.toString();
			var exists = this.options.find(function(item){
				return item['value'] == data['value'];
			});
			if(!exists)
			{
				this.options.unshift({
	    		    value:data.value,
	    			label:data.label
	    		});
				this.$emit('update:options', this.options);
			}
			if(this.$attrs.hasOwnProperty('value') && this.$attrs['value'].indexOf(data.value)<0)
			{
				if(!this.$attrs['multiple-limit'])
				{
					this.$attrs['value'].push(data.value);
				}else if(this.$attrs['value'].length<this.$attrs['multiple-limit']){
					this.$attrs['value'].push(data.value);
				}
			}
		}
     }
});

Vue.component('set-auth-table', {
	props:{
		'items':{},
		'value':{}
	},
	template:`<table class="set-auth-table" v-bind="$attrs">
    	<tbody>
    	  <template v-for="item in items" :key="item.id">
    	     <tr class="bg-theme">
    			<td colspan="2"><el-checkbox :indeterminate="checkItems[item.id].indeterminate" v-model="checkItems[item.id].value" @change="function(value){onChange(item,value)}">{{item.label}}</el-checkbox></td>
    		</tr>
    		<template v-if="item.children">
	    		<tr v-for="item2 in item.children" :key="item2.id">
					<td width="15%" v-if="item2" :colspan="item2.children?1:2"><el-checkbox :indeterminate="checkItems[item2.id].indeterminate" v-model="checkItems[item2.id].value" @change="function(value){onChange(item2,value)}">{{item2.label}}</el-checkbox></td>
					<template v-if="item2.children">
						<td>
						    <el-checkbox v-for="item3 in item2.children" :key="item3.id" @change="function(value){onChange(item3,value)}" v-model="checkItems[item3.id].value">{{item3.label}}</el-checkbox>
						</td>
					</template>
				</tr>
    		</template>
    	  </template>
    	</tbody>
    </table>`,
    data:function(){
		var data = {
			checkItems:{}
		};
		var each = function(nodes,level,parent){
			if(!level){
				level = 0;
			}
	    	for(var i=0;i<nodes.length;i++){
	    		var node = nodes[i];
				data.checkItems[node.id] = {value:false,indeterminate:false,item:node};
	    		node._parent =  parent;
	    		if(node.children && node.children.length>0)
				{
	    			each(node.children,level+1,node);
				}
	        }
	    }
		each(this.items);
		return data;
    },
    mounted:function(){
    	if(this.value)
		{
			for(var i=0;i<this.value.length;i++)
			{
				Vue.set(this.checkItems[this.value[i]],'value',true);
			}
		}
    },
    methods:{
		checkRelation:function(item,value){
			var _this = this;
			if(this.checkItems[item.id].indeterminate)
			{
				Vue.set(this.checkItems[item.id],'indeterminate',false);
			}
			var checkChildren = function(item){
				if(item.children)
				{
					for(var key in item.children)
					{
						Vue.set(_this.checkItems[item.children[key].id],'value',value);
						Vue.set(_this.checkItems[item.children[key].id],'indeterminate',false);
						checkChildren(item.children[key]);
					}
				}
			}
			var checkParent = function(item){
				if(item._parent)
				{	
					var setValue = value;
					if(!setValue)
					{
						var checkCount = 0;
						for(var i=0;i<item._parent.children.length;i++)
						{
							if(_this.checkItems[item._parent.children[i].id].value)
							{
								checkCount ++;
							}
						}
						if(checkCount>0)
						{
							setValue = true;
						}
					}
					Vue.set(_this.checkItems[item._parent.id],'value',setValue);
					checkParent(item._parent);
				}
			}
			checkChildren(item);
			checkParent(item);
		},
		getCheckItemList:function(items){
			if(!items)
			{
				items = this.items;
			}
			var list = [];
			for(var i=0;i<items.length;i++)
			{
				if(this.checkItems[items[i].id].value)
				{
					list.push(items[i]);
				}
				if(items[i].children && items[i].children.length>0)
				{
					list = list.concat(this.getCheckItemList(items[i].children));
				}
			}
			return list;
		},
		getValue:function(){
			var value = [];
			var checkList = this.getCheckItemList();
			for(var i =0;i<checkList.length;i++)
			{
				if(this.checkItems[checkList[i].id].value)
				{
					value.push(checkList[i].id);
				}
			}
			return value;
		},
		onChange:function(item,value){
			this.checkRelation(item,value);
		}
	}
});


Vue.component('goods-sku-table',{
	props:{
		attrAssign:{},
		extRowProps:{},
		tableData:[]
	},
	template:`
		<div class="goods-sku-container">
		<slot name="header"></slot>
		<el-table v-if="tableData.length>0" :data="tableData">
	    <el-table-column  width="220px" v-for="column in columns" :key="column.prop"
	      :prop="column.prop"
	      :label="column.label">
	    </el-table-column>
	    <slot name="columns"></slot>
	  </el-table>
			<slot name="bottom"></slot>
		  </div>`,
	watch:{
		attrAssign:function(value){
			this.setTable();
		}
	},
	data:function(){
		return {
			columns:[],
			tableData:[]
		};
	},
	methods:{
		setTable:function(){
			var tableData = [];
			var columns = [];
			var items = [];
			for(var i in this.attrAssign)
			{
				columns.push({
					label:this.attrAssign[i]['attr']['label'],
					prop:this.attrAssign[i]['attr']['value']
				});
				items.push(this.attrAssign[i]['item']);
			}
			if(items.length>0)
			{
				var rows = $util.cartesian(items)
				for(var i in rows)
				{
					var skuId = [];
					for(var j in rows[i])
					{
						skuId.push(rows[i][j]['attrValue']+'_'+rows[i][j]['value'])
					}
					skuId = skuId.join(':');
					row = this.tableData.find(function(item){
						return item['sku_id'] == skuId;
					});
					if(!row)
					{
						var common = {};
						var row = {};
						for(var j in rows[i])
						{
							row[rows[i][j]['attrValue']] = rows[i][j]['label'];
							if(rows[i][j]['common'])
							{
								Object.assign(common,rows[i][j]['common']);
							}
						}
						row['sku_id'] = skuId;
						row['common'] = common;
						if(this.extRowProps)
						{
							Object.assign(row,this.extRowProps(row));
						}
					}
					tableData.push(row);
				}
			}
			this.columns = columns;
			this.tableData = tableData;
			this.$emit('update:table-data', this.tableData);
		}
	}
});

Vue.component('goods-attr-assign',{
	props:{
		attrOptionsUrl:{},
		attrFastCreateUrl:{},
		itemOptionsUrl:{},
		itemFastCreateUrl:{},
		limit:{},
		assignData:{}
	},
	template:`
		<div class="goods-attr-assign-container">
			<table class="goods-attr-assign-table">
				<template v-for="(row,index) in tableData" :key="row.value">
    				<tr class="goods-attr-assign-row-title">
    					<td class="goods-attr-assign-row-label">规格名</td>
    					<td class="goods-attr-assign-row-value">
                    		<el-tag class="goods-attr-assign-item" effect="dark" >
							{{row.label}}
                    		</el-tag>
						</td>
    				</tr>
            		<tr>
                		<td class="goods-attr-assign-row-label">规格值</td>
                		<td class="goods-attr-assign-row-value">
                        		<el-tag v-for="(item,index) in row['items']" :key="item.value" class="goods-attr-assign-item" effect="plain">
                        		{{item.label}}
                        		</el-tag>
                    		<el-popover v-model="row['showItemProp']"  @show="onShowItem(row)" placement="right" trigger="click">
                        		<el-button class="goods-attr-assign-item" slot="reference" type="text">添加</el-button>
									<select-box :key="row.value" :options.sync="row['itemOptions']" v-model="row['itemOptionsSelect']" :fast-create-url="itemFastCreateUrl" :fast-create-data="{attr_id:row['value']}" :options-url="itemOptionsUrl" :options-params="{attr_id:row['value']}" :clearable="true" :multiple="true" :collapse-tags="true" placeholder="规格名"></select-box>
                        		<el-button type="primary" @click="onConfirmItem(row)">确定</el-button>
                          </el-popover>
                		</td>
                	</tr>
    			</template>
        		<tr class="goods-attr-assign-row-title">
            		<td colspan="2" class="goods-attr-assign-last-row">
            			<el-popover v-model="showAttrProp" @show="onShowAttr" placement="right" trigger="click">
                        <el-button slot="reference">添加规格</el-button>
							<select-box :multiple-limit="limit" :options.sync="attrOptions" v-model="attrOptionsSelect" :fast-create-url="attrFastCreateUrl" :options-url="attrOptionsUrl" :clearable="true" :multiple="true" :collapse-tags="true" placeholder="规格名"></select-box>
                		<el-button type="primary" @click="onConfirmAttr">确定</el-button>
                      </el-popover>
					</td>
            	</tr>
			</table>
		</div>
		`,
    data:function(){
		return {
			showAttrProp:false,
			attrOptions:[],
			attrOptionsSelect:[],
			attrOptionsValue:[],
			tableData:[]
		};
    },
    methods:{
    	onShowAttr:function(){
			this.attrOptionsSelect = this.attrOptionsValue.concat();
		},
		onShowItem:function(row){
			row.itemOptionsSelect = row.itemOptionsValue.concat();
		},
		onConfirmAttr:function(){
			this.attrOptionsValue = this.attrOptionsSelect.concat();
			this.showAttrProp = false;
			this.setAttrTableData();
		},
		onConfirmItem:function(row){
			row.itemOptionsValue = row.itemOptionsSelect.concat();
			row['showItemProp'] = false;
			this.setItemData(row);
		},
		onRemoveAttr:function(index){
			this.tableData.splice(index,1);
			this.attrOptionsValue.splice(index,1);
			this.setAttrTableData();
			this.setAssignData();
		},
		onRemoveItem:function(row,index){
			row['items'].splice(index,1);
			row['itemOptionsValue'].splice(index,1);
			this.setItemData(row);
			this.setAssignData();
		},
		setAttrTableData:function(){
			var _this = this;
			var tableData = [];
        	for(var index in this.attrOptionsValue)
        	{
            	var attrOption = this.attrOptions.find(function(item){
						return item['value'] == _this.attrOptionsValue[index];
                 });
                if(attrOption)
                {
                    var existRow = this.tableData.find(function(item){
						return item['value'] == attrOption['value'];
                    });
            		tableData.push(existRow?existRow:{
                		value:attrOption['value'],
                		label:attrOption['label'],
                		items:[],
                		showItemProp:false,
                		itemOptions: [],
                		itemOptionsSelect:[],
                		itemOptionsValue:[],
                	});
                }
            }
            this.tableData = tableData;
            this.setAssignData();
		},
		setItemData:function(row){
			var itemData = [];
			for(var index in row['itemOptionsValue'])
        	{
				var itemOption = row['itemOptions'].find(function(item){
					return item['value'] == row['itemOptionsValue'][index];
                 });
                if(itemOption)
                {
                	itemData.push({
                		value:itemOption['value'],
                		label:itemOption['label']
                	});
                }
        	}
        	row['items'] = itemData;
        	this.setAssignData();
		},
		setFormData:function(form){
			var tableData = [];
        	for(var i in form)
        	{
            	this.attrOptionsValue.push(form[i]['attr']['id']);
            	this.attrOptions.push({value:form[i]['attr']['id'],label:form[i]['attr']['name']});
				var items = [];
				var itemOptions = [];
				var itemOptionsValue = [];
				for(var j in form[i]['items'])
				{
					items.push({value:form[i]['items'][j]['id'],label:form[i]['items'][j]['name']});
					itemOptions.push({value:form[i]['items'][j]['id'],label:form[i]['items'][j]['name']});
					itemOptionsValue.push(form[i]['items'][j]['id']);
				}
        		tableData.push({
            		value:form[i]['attr']['id'],
            		label:form[i]['attr']['name'],
            		items:items,
            		showItemProp:false,
            		itemOptions: itemOptions,
            		itemOptionsSelect:[],
            		itemOptionsValue:itemOptionsValue,
            	});
            }
            this.tableData = tableData;
            this.setAssignData();
		},
		getFormData:function(){
			var form = [];
			for(var i in this.tableData)
			{
				var itemId = [];
				for(var j in this.tableData[i]['items'])
				{
					itemId.push(this.tableData[i]['items'][j]['value']);
				}
				if(itemId.length>0)
				{
					form.push({'attr_id':this.tableData[i].value,'item_id':itemId});
				}
			}
			return form;
		},
		setAssignData:function(){
			var data = [];
			for(var i in this.tableData)
			{
				var item = [];
				for(var j in this.tableData[i]['items'])
				{
					item.push({value:this.tableData[i]['items'][j]['value'],label:this.tableData[i]['items'][j]['label'],attrValue:this.tableData[i].value});
				}
				if(item.length>0)
				{
					data.push({
						'attr':{value:this.tableData[i].value,label:this.tableData[i].label},
						'item':item
					});
				}
			}
			this.assignData = data;
			this.$emit('update:assign-data', data);
		}
    }
});


Vue.$page = {
	el:'#page',
	instance:null,
	mixin : [],
	init : function() {
		var instance = this.instance;
		if(!instance)
		{
			this.mixin.push({
				el : this.el
			});
			instance = new Vue({
				mixins:this.mixin
			});
			this.instanc = instance;
		}
		this.instance = instance;
	}
}

$util = {
	url:{
		stringify:function(url,params){
			if(url.indexOf('?')==-1)
			{
				url += "?" ;
			}
			url += Qs.stringify(params);
			return url;
		},
		getHashParams:function(key){
			var hash = location.hash.substr(1,location.hash.length);
			if(!hash)
			{
				return null;
			}
			var params = Qs.parse(hash);
			if(key)
			{
				return params[key];
			}else{
				return params;
			}
		},
		setHashParams:function(params){
			location.hash = Qs.stringify(params);
		}
	},
	request:function(url,options){
		options = options || {}
		if(options.method && options.method.toLowerCase() == "post")
		{
			if(options.data)
			{
				options.data = Qs.stringify(options.data);
			}
		}
		options = Object.assign({
			  url:url,
			  showErrorMessage:true
		},options)
		axios.request(options).then(function(response){
			if(options.success)
			{
				options.success(response);
			}
			if(options.complete)
			{
				options.complete(response);
			}
		}).catch(function(error){
			var response = error.response?error.response:null;
			if(options.showErrorMessage)
			{
				Vue.prototype.$notify({
					  type: "error",
			          title: '提示',
			          message: '访问出错啦，请稍后再试',
			          position: 'bottom-right'
			    });
			}
			if(options.error)
			{
				options.error(response);
			}
			if(options.complete)
			{
				options.complete(response);
			}
			throw error;
		});
	},
	generateTreeList:function(data){
		if(!data)
		{
			return [];
		}
		var each = function(data,level)
		{
			if(!level)
			{
				level = 0;
			}
			var result = [];
	    	for(var i=0;i<data.length;i++)
	    	{
	    		var node = data[i];
	    		node._nodeLevel = level;
	    		result.push(node);
	    		if(node.children && node.children.length>0)
				{
					result = result.concat(each(node.children,level+1));
				}
	        }
	        return result;
	    }
		var result = each(data);
		return result;
	},
	confirm:function(text,options){
		var options = Object.assign({},{
	        title:'提示',
	        confirmCallback:function(){}
	      },options);
		Vue.prototype.$confirm(text, options.title, options).then(options.confirmCallback).catch(() => {
        }); 
	},
	cartesian:function(arr) {
	    return Array.prototype.reduce.call(arr,       function(a, b) {
	    var ret = [];
	        a.forEach(function(a) {
	        b.forEach(function(b) {
	        ret.push(a.concat([b]));
	      });
	    });
	   return ret;
	  }, [[]]);
	}
};

(function(){
	if(axios)
	{
		axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
		axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
	}
})();

(function (callback) {
    if (document.addEventListener) {
                document.addEventListener('DOMContentLoaded', function () {
                    document.removeEventListener('DOMContentLoaded', arguments.callee, false);
                    callback();
                }, false)
            }
    else if (document.attachEvent) {
        document.attachEvent('onreadystatechange', function () {
              if (document.readyState == "complete") {
                        document.detachEvent("onreadystatechange", arguments.callee);
                        callback();
               }
        })
    }
    else if (document.lastChild == document.body) {
        callback();
    }
})(function(){
	Vue.$page.init();
})