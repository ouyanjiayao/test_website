Vue.component('goods-attr-edit',{
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
                    		<el-tag class="goods-attr-assign-item" effect="dark" closable :disable-transitions="true" @close="onRemoveAttr(index)">
							{{row.label}}
                    		</el-tag>
                    		
						</td>
    				</tr>
            		<tr>
                		<td class="goods-attr-assign-row-label">规格值</td>
                		<td class="goods-attr-assign-row-value">
                        		<el-tag v-for="(item,index) in row['items']" :key="item.value" class="goods-attr-assign-item" effect="plain" closable :disable-transitions="true" @close="onRemoveItem(row,index)">
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