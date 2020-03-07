<?php
use common\librarys\Url;
use common\librarys\HtmlHelper;
use common\models\Goods;
use common\models\GoodsAttrConfig;
use common\models\GoodsAttr;
use common\models\GoodsAttrItem;

$this->title = !$model?"新建商品":"编辑商品";
$frameConfigs = [
    'paths'=>[
        '商品管理',
        [
            'name'=>'商品管理',
            'url'=>Url::toRoute('manage').'#type='.$type
        ],
        $this->title
    ],
    'activeMenu'=>[2,2]
];
?>
<style>
.cb-weight{
	color: red !important;
	font-weight: 800;
}

.cb-weight .el-form-item__label{
	color: red !important;
	font-weight: 800;
}
</style>
<script>
Vue.component('set-sku-price',{
	props:{
		attrConfig:{},
		attrAssignData:{}
	},
	computed:{
		getSalePrice:function(){
			return function(row){
				return getSalePrice(row['item']['cost'],row['item']['sale_scale']);
			}
		}
	},
	template:`<goods-sku-table :table-data.sync="skuTableData" :attr-assign="attrAssignData" :ext-row-props="skuExtRowProp">
              	<template slot="columns">
          		<template v-if="attrConfig.price_count_type == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>">
              		<el-table-column width="220px" label="成本价 /件" header-align="left" align="left" >
                    	<template slot-scope="scope">
                    		<el-input-number v-model="scope.row['item']['cost']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                    	</template>
                    </el-table-column>
                    <el-table-column  width="220px" label="售价比" header-align="left" align="left" >
                    	<template slot-scope="scope">
                    		<el-input-number :disabled="!attrConfig.auto_create_sale_price" v-model="scope.row['item']['sale_scale']" :precision="2" :step="0.01" :min="0.01" size="small"></el-input-number>
                    	</template>
                    </el-table-column>
            		<el-table-column label="销售价" header-align="left" align="left" >
                    	<template slot-scope="scope">
    						<template v-if="attrConfig['auto_create_sale_price'] ==<?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE?> ">
								￥{{getSalePrice(scope.row).toFixed(2)}}
							</template>
							<template v-else>
								<el-input-number v-model="scope.row['item']['sale_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
							</template>
                    	</template>
                    </el-table-column>
                </template>
                <template v-else-if="attrConfig.price_count_type == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>">
              		<el-table-column width="220px" label="成本价 /斤" header-align="left" align="left" >
                    	<template slot-scope="scope">
                    		<el-input-number v-model="scope.row['weight']['cost']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                    	</template>
                    </el-table-column>
                    <el-table-column width="220px" label="售价比" header-align="left" align="left" >
                    	<template slot-scope="scope">
                    		<el-input-number :disabled="!attrConfig.auto_create_sale_price" v-model="scope.row['weight']['sale_scale']" :precision="2" :step="0.01" :min="0.01" size="small"></el-input-number>
                    	</template>
                    </el-table-column>
            		<el-table-column label="销售价" header-align="left" align="left" >
                    	<template slot-scope="scope">
                    		-
                    	</template>
                    </el-table-column>
                </template>
          	</template>
          	<div class="goods-sku-table-bottom" slot="bottom">批量设置：
              	<el-popover v-model="showBatchCost" placement="bottom" trigger="click" @show="batchCost=0">
                    <el-button slot="reference" size="medium" type="text">成本价</el-button>
                    <el-input-number v-model="batchCost" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                    <el-button size="small" type="primary" @click="onBatchSet('cost')">确定</el-button>
                  </el-popover>
                  <el-popover v-model="showBatchSaleScale" placement="right" trigger="click" @show="batchSaleScale=1">
                    <el-button :disabled="!attrConfig.auto_create_sale_price" slot="reference" size="medium" type="text">售价比</el-button>
                    <el-input-number v-model="batchSaleScale" :precision="2" :step="0.01" :min="0.01" size="small"></el-input-number>
                    <el-button size="small" type="primary" @click="onBatchSet('saleScale')">确定</el-button>
                  </el-popover>
          	</div>
          </goods-sku-table>`,
    watch:{
    	skuTableData:{
    		handler:function(value){
    			this.$emit('update:sku-table-data', this.skuTableData);
            }
        }
    },
    data:function(){
    	return {
        	showBatchCost:false,
			batchCost:0,
			showBatchSaleScale:false,
			batchSaleScale:1,
			skuTableData:[]
        };
    },
    methods:{
    	skuExtRowProp:function(){
			return {
				'item':{
					cost:0,
					sale_scale:1,
					sale_price:0
				},
				'weight':{
					cost:0,
					sale_scale:1,
				}
			};
		},
		onBatchSet:function(type){
			var extRowKey = this.attrConfig['price_count_type']==<?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>?'item':'weight';
			for(var i in this.skuTableData)
			{
				if(type == 'cost')
				{
					this.skuTableData[i][extRowKey]['cost'] = this.batchCost;
				}else if(type =='saleScale')
				{
					this.skuTableData[i][extRowKey]['sale_scale'] = this.batchSaleScale;
				}
			}
			this.showBatchCost = false;
			this.showBatchSaleScale = false;
		},
		getFormData:function(){
			var form = [];
			var extRowKey = this.attrConfig['price_count_type']==<?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>?'item':'weight';
			for(var i in this.skuTableData)
			{
				var item = {
					sku_id:this.skuTableData[i]['sku_id'],
					cost:this.skuTableData[i][extRowKey]['cost'],
					sale_scale:this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE?>?this.skuTableData[i][extRowKey]['sale_scale']:null
				};
				if(extRowKey == 'item')
				{
					if(this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE?>){
						item['sale_price'] = getSalePrice(this.skuTableData[i]['item']['cost'],this.skuTableData[i]['item']['sale_scale']);
					}else{
						item['sale_price'] = this.skuTableData[i][extRowKey]['sale_price'];
					}
				}
				form.push(item);
			}
			return form;
		},
		setFormData:function(data){
			if(!data)
				return;
			var _this = this;
			this.$nextTick(function(){
				for(var i in data)
				{
					var row = _this.skuTableData.find(function(item){
						return item['sku_id'] == data[i]['sku_id'];
					});
					if(row)
					{
						var extRowKey = _this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>?'item':'weight';
						row[extRowKey]['cost'] = data[i]['cost'];
						row[extRowKey]['sale_scale'] = data[i]['sale_scale']?data[i]['sale_scale']:1;
						if(extRowKey == 'item')
						{
							if(this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_FALSE?>)
							{
								row[extRowKey]['sale_price'] = data[i]['sale_price'];
							}
						}
					}
				}
			});
		}
    }
});

Vue.component('set-sys-attr-weight',{
	props:{
		attrAssignData:{},
		itemFastCreateUrl:{},
		systemAttrId:{},
		skuPriceTableData:{},
		attrConfig:{}
	},
	watch:{
		attrAssignData:function(value){
			this.setCompleteAssignData();
		},
		weightValue:function(){
			this.setCompleteAssignData();
		},
		skuTableData:function(){
			this.$emit('update:sku-table-data',this.skuTableData);
		}
	},
    computed:{
    	hasCustomAttr:function(){
			return this.attrAssignData.length>0;
		},
    	getCostPrice:function(){
        	var _this = this;
			return function (row) {
				return _this.computedSku(row,'cost');
            }
		},
		getSalePrice:function(){
			var _this = this;
			return function (row) {
				return _this.computedSku(row,'salePrice');
            }
		}
    },
	template:`<div class='goods-sys-attr-container'>
		<div class="goods-sys-attr-title-block">重量规格</div>
		<goods-sku-table :table-data.sync="skuTableData" :attr-assign="completeAssignData" :ext-row-props="skuExtRowProp">
		<template slot="columns">
      		<el-table-column width="220px" label="成本价" header-align="left" align="left" >
            	<template slot-scope="scope">
					￥{{getCostPrice(scope.row).toFixed(2)}}
            	</template>
            </el-table-column>
            <el-table-column label="销售价" header-align="left" align="left" >
            	<template slot-scope="scope">
    				<template v-if="attrConfig.auto_create_sale_price == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE ?>">
						￥{{getSalePrice(scope.row).toFixed(2)}}
					</template>
					<template v-else>
						<el-input-number v-model="scope.row['sale_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
					</template>
    			</template>
            </el-table-column>
      	</template>
      	<div class="goods-sku-table-bottom" slot="bottom">
    		<template v-if="attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_FALSE?> && skuTableData.length>0">
    		批量设置：
          	<el-popover v-model="showBatchSalePrice" placement="right" trigger="click" @show="batchSalePrice=0">
            <el-button slot="reference" size="medium" type="text">销售价</el-button>
            <el-input-number v-model="batchSalePrice" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
            <el-button size="small" type="primary" @click="onBatchSet('salePrice')">确定</el-button>
    		</el-popover>
    		</template>
    		<div class="sys-attr-item-wrap" v-if="weightValue.length>0">
    		<el-tag v-for="(item,index) in weightValue" :key="item.value" class="goods-attr-assign-item" effect="plain" closable :disable-transitions="true" @close="onRemove(index)">{{item.label}}</el-tag>
			<div class="clear"></div>
    		</div>
    		<el-popover v-model="showFastCreatePop" placement="right" trigger="click" @show="value='';desc=''">
            <el-button slot="reference" size="medium" type="text">添加重量</el-button>
    		<el-form label-width="45px">
        		<el-form-item label="单位">
					<el-input-number v-model="value" :precision="0" :min="0" size="small"></el-input-number>&nbsp;&nbsp;G（克）
        	  </el-form-item>
        		<el-form-item label="说明">
		 			<el-input v-model="desc" size="small"></el-input>
        		</el-form-item>
    		</el-form>
			<div class="pop-button-wrap">
				<el-button type="primary" @click="submit" :disabled="fastCreateLoading">确定</el-button>
			</div>
          </el-popover>
      	</div>
  </goods-sku-table></div>`,
	data:function(){
		return {
			showFastCreatePop:false,
			value:'',
			desc:'',
			fastCreateLoading:false,
			skuTableData:[],
			weightValue:[],
			completeAssignData:[],
			showBatchSalePrice:false,
			batchSalePrice:0
		};
	},
	methods:{
		onBatchSet:function(type){
			for(var i in this.skuTableData)
			{
				if(type == 'salePrice')
				{
					this.skuTableData[i]['sale_price'] = this.batchSalePrice;
				}
			}
			this.showBatchSalePrice = false;
		},
		computedSku:function(row,type){
			if(this.hasCustomAttr)
			{
				var priceSkuId = row['sku_id'].split(':'); 
				priceSkuId.splice(priceSkuId.length-1,1);
				priceSkuId = priceSkuId.join(':');
				var item = this.skuPriceTableData.find(function(item){
					return item['sku_id'] == priceSkuId;
				});
				if(item)
				{
					if(type == 'cost')
					{
						return getWeightCostPrice(item['weight']['cost'],row['common']['weight']);
					}else if(type == 'salePrice')
					{
						return getWeightSalePrice(item['weight']['cost'],row['common']['weight'],item['weight']['sale_scale']);
					}
				}
			}else{
				if(type == 'cost')
				{
					return getWeightCostPrice(this.attrConfig['weight']['cost'],row['common']['weight']);
				}else if(type == 'salePrice')
				{
					return getWeightSalePrice(this.attrConfig['weight']['cost'],row['common']['weight'],this.attrConfig['weight']['sale_scale']);
				}
			}
			return null;
		},
		skuExtRowProp:function(){
			return {
				sale_price:0
			};
		},
		submit:function(){
			if(!this.value)
			{
				this.showFastCreatePop = false;
				return;
			}
			var _this = this;
			this.fastCreateLoading = true;
			$util.request(this.itemFastCreateUrl,{
				method:'post',
				params:{
					system_attr_id:this.systemAttrId
				},
				data:{
					value:this.value,
					desc:this.desc
				},
				success:function(responce){
					_this.showFastCreatePop = false;
					var exists = _this.weightValue.find(function(item){
						return item['value'] == responce.data['value'];
					});
					if(!exists)
						_this.weightValue.push(responce.data);
				},
				complete:function(){
					_this.fastCreateLoading = false;
				}
			});
		},
		onRemove:function(index){
			this.weightValue.splice(index,1);
		},
		setCompleteAssignData:function(){
			var _this = this;
			var completeAssignData = [];
			if(_this.weightValue.length>0)
			{
				for(var i in _this.attrAssignData){
					completeAssignData.push(_this.attrAssignData[i]);
				}
				var item = [];
				for(var i in _this.weightValue)
				{
					item.push({
						attrValue:'<?=GoodsAttr::SYSTEM_WEIGHT_ID?>',
						value:_this.weightValue[i]['value'],
						label:_this.weightValue[i]['label'],
						common:{
							weight:_this.weightValue[i]['weight']
						}
					});
				}
				completeAssignData.push({
					attr:{
						label:'重量',
						value:'<?=GoodsAttr::SYSTEM_WEIGHT_ID?>',
					},
					item:item
				});
			}
			_this.completeAssignData = completeAssignData;
			_this.$emit('update:weight-value', _this.weightValue);
		},
		setFormData:function(data){
			if(!data)
				return;
			var _this = this;
			var weightValue = [];
			for(var i in data.assign)
			{
				weightValue.push({
					value:data.assign[i].id,
					label:data.assign[i].name,
					weight:data.assign[i].weight
				});
			}
			this.weightValue = weightValue;
			this.$nextTick(function(){
				for(var i in data['sku'])
				{
					var row = _this.skuTableData.find(function(item){
						return item['sku_id'] == data['sku'][i]['sku_id'];
					});
					if(row)
					{
						if(_this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_FALSE?>)
						{
							row['sale_price'] = data['sku'][i]['sale_price'];
						}
					}
				}
			});
		},
		getFormData:function(){
			var form = {};
			var assign = [];
			var sku = [];
			for(var i in this.weightValue)
			{
				assign.push(this.weightValue[i].value);
			}
			for(var i in this.skuTableData)
			{
				var salePrice;
				if(this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE ?>)
				{
					salePrice = this.computedSku(this.skuTableData[i],'salePrice');
				}else{
					salePrice = this.skuTableData[i]['sale_price'];
				}
				sku.push({
					sku_id:this.skuTableData[i]['sku_id'],
					sale_price:salePrice
				});
			}
			form['assign'] = assign;
			form['sku'] = sku;
			return form;
		}
	}
});

Vue.component('set-sys-attr-handle',{
	props:{
		attrAssignData:{},
		itemFastCreateUrl:{},
		itemOptionsUrl:{},
		systemAttrId:{},
		attrConfig:{}
	},
	watch:{
		attrAssignData:function(value){
			this.setCompleteAssignData();
		},
		handleValue:function(){
			this.setCompleteAssignData();
		},
		skuTableData:function(){
			this.$emit('update:sku-table-data',this.skuTableData);
		}
	},
	template:`<div class='goods-sys-attr-container'>
		<div class="goods-sys-attr-title-block">加工方式规格</div>
			<goods-sku-table :table-data.sync="skuTableData" :attr-assign="completeAssignData" :ext-row-props="skuExtRowProp">
            		<template slot="columns">
                		<el-table-column v-if="attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM?>" label="加工价格 /件" header-align="left" align="left" >
                    		<template slot-scope="scope">
                    			<el-input-number v-model="scope.row['item']['handle_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                    		</template>
                        </el-table-column>
                		<el-table-column v-if="attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT?>" label="加工价格 /斤" header-align="left" align="left" >
                    		<template slot-scope="scope">
                    			<el-input-number v-model="scope.row['weight']['handle_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                    		</template>
                        </el-table-column>
                  	</template>
                  	<div class="goods-sku-table-bottom" slot="bottom">
      					<template v-if="skuTableData.length>0">
                		批量设置：
                      	<el-popover v-model="showBatchHandlePrice" placement="right" trigger="click" @show="batchHandlePrice=0">
                        <el-button slot="reference" size="medium" type="text">加工价格</el-button>
                        <el-input-number v-model="batchHandlePrice" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                        <el-button size="small" type="primary" @click="onBatchSet('handlePrice')">确定</el-button>
                		</el-popover>
						</template>
                		<div class="sys-attr-item-wrap" v-if="handleValue.length>0">
                		<el-tag v-for="(item,index) in handleValue" :key="item.value" class="goods-attr-assign-item" effect="plain" closable :disable-transitions="true" @close="onRemove(index)">{{item.label}}</el-tag>
            			<div class="clear"></div>
                		</div>
                		<el-popover v-model="showFastCreatePop" placement="right" trigger="click" @show="itemOptionsSelect = itemOptionsValue.concat();">
                        <el-button slot="reference" size="medium" type="text">添加加工方式</el-button>
            			<select-box :options.sync="itemOptions" v-model="itemOptionsSelect" 
            				:fast-create-url="itemFastCreateUrl" :fast-create-data="{attr_id:systemAttrId}" 
            				:options-url="itemOptionsUrl" :options-params="{attr_id:systemAttrId}" 
            				:clearable="true" :multiple="true" :collapse-tags="true" placeholder="加工方式"></select-box>
                        <el-button size="small" type="primary" @click="submit" :disabled="fastCreateLoading">确定</el-button>
                      </el-popover>
                  	</div>
              </goods-sku-table></div>`,
  	data:function(){
		return {
			showFastCreatePop:false,
			itemOptions:[],
			itemOptionsSelect:[],
			itemOptionsValue:[],
			fastCreateLoading:false,
			skuTableData:[],
			handleValue:[],
			completeAssignData:[],
			showBatchHandlePrice:false,
			batchHandlePrice:0
		};
	},
	methods:{
		onBatchSet:function(type){
			var extRowKey = this.attrConfig['price_count_type']==<?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>?'item':'weight';
			for(var i in this.skuTableData)
			{
				if(type == 'handlePrice')
				{
					this.skuTableData[i][extRowKey]['handle_price'] = this.batchHandlePrice;
				}
			}
			this.showBatchHandlePrice = false;
		},
		skuExtRowProp:function(){
			return {
				'item':{
					'handle_price':0
				},
				'weight':{
					'handle_price':0
				}
			};
		},
		submit:function(){
			this.itemOptionsValue = this.itemOptionsSelect;
			this.showFastCreatePop = false;
			this.setHandleValue();
		},
		onRemove:function(index){
			this.itemOptionsValue.splice(index,1);
			this.handleValue.splice(index,1);
		},
		setHandleValue:function(){
			var _this = this;
			var skuTableData = [];
			var handleValue =[];
        	for(var index in this.itemOptionsValue)
        	{
            	var attrOption = this.itemOptions.find(function(item){
						return item['value'] == _this.itemOptionsValue[index];
                 });
                if(attrOption)
                {
                	handleValue.push(attrOption);
                }
            }
            this.handleValue = handleValue;
		},
		setCompleteAssignData:function(){
			var _this = this;
			var completeAssignData = [];
			if(_this.handleValue.length>0)
			{
				for(var i in _this.attrAssignData){
					completeAssignData.push(_this.attrAssignData[i]);
				}
				var item = [];
				for(var i in _this.handleValue)
				{
					item.push({
						attrValue:'<?=GoodsAttr::SYSTEM_HANDLE_ID ?>',
						value:_this.handleValue[i]['value'],
						label:_this.handleValue[i]['label']
					});
				}
				completeAssignData.push({
					attr:{
						label:'加工方式',
						value:'<?=GoodsAttr::SYSTEM_HANDLE_ID ?>',
					},
					item:item
				});
			}
			_this.completeAssignData = completeAssignData;
			_this.$emit('update:handle-value', _this.handleValue);
		},
		setFormData:function(data){
			if(!data)
				return;
			var _this = this;
			var handleValue = [];
			var itemOptions = [];
			var itemOptionsValue = [];
			for(var i in data.assign)
			{
				handleValue.push({
					value:data.assign[i].id,
					label:data.assign[i].name
				});
				itemOptions.push({
					value:data.assign[i].id,
					label:data.assign[i].name
				});
				itemOptionsValue.push(data.assign[i].id);
			}
			this.handleValue = handleValue;
			this.itemOptions = itemOptions;
			this.itemOptionsValue = itemOptionsValue;
			this.$nextTick(function(){
				for(var i in data['sku'])
				{
					var row = _this.skuTableData.find(function(item){
						return item['sku_id'] == data['sku'][i]['sku_id'];
					});
					if(row)
					{
						var extRowKey = _this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>?'item':'weight';
						row[extRowKey]['handle_price'] = data['sku'][i]['handle_price'];
					}
				}
			});
		},
		getFormData:function(){
			var form = {};
			var assign = [];
			var sku = [];
			for(var i in this.handleValue)
			{
				assign.push(this.handleValue[i].value);
			}
			for(var i in this.skuTableData)
			{
				var handlePrice;
				if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>)
				{
					handlePrice = this.skuTableData[i]['item']['handle_price'];
				}else if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>){
					handlePrice = this.skuTableData[i]['weight']['handle_price'];
				}
				sku.push({
					sku_id:this.skuTableData[i]['sku_id'],
					handle_price:handlePrice
				});
			}
			form['assign'] = assign;
			form['sku'] = sku;
			return form;
		}
	}
});

Vue.component('set-sys-attr-wash',{
	props:{
		attrAssignData:{},
		attrConfig:{}
	},
	template:`<div class='goods-sys-attr-container'>
		<div class="goods-sys-attr-title-block">清洗规格</div>
			<goods-sku-table :attr-assign="attrAssignData" :table-data.sync="skuTableData" :ext-row-props="skuExtRowProp">
            		<template slot="columns">
                		<el-table-column v-if="attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM?>" label="清洗价格 /件" header-align="left" align="left" >
                    		<template slot-scope="scope">
                    			<el-input-number v-model="scope.row['item']['wash_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                    		</template>
                        </el-table-column>
                		<el-table-column v-if="attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT?>" label="清洗价格 /斤" header-align="left" align="left" >
                    		<template slot-scope="scope">
                    			<el-input-number v-model="scope.row['weight']['wash_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                    		</template>
                        </el-table-column>
                  	</template>
                  	<div class="goods-sku-table-bottom" slot="bottom">
      					<template v-if="skuTableData.length>0">
                		批量设置：
                      	<el-popover v-model="showBatchWashPrice" placement="right" trigger="click" @show="batchWashPrice=0">
                        <el-button slot="reference" size="medium" type="text">清洗价格</el-button>
                        <el-input-number v-model="batchWashPrice" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
                        <el-button size="small" type="primary" @click="onBatchSet('washPrice')">确定</el-button>
                		</el-popover>
						</template>
						<template v-else>
                    		<template v-if="attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>">
                    		清洗价格：<el-input-number v-model="attrConfig['item']['wash_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>&nbsp;/件
                    		</template>
                    		<template v-else-if="attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>">
                    		清洗价格：<el-input-number v-model="attrConfig['weight']['wash_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>&nbsp;/斤
                    		</template>
						</template>
                  	</div>
              </goods-sku-table></div>`,
      data:function(){
    		return {
    			skuTableData:[],
    			skuWashValue:[],
    			showBatchWashPrice:false,
    			batchWashPrice:0
    		};
    	},
    	watch:{
    		skuTableData:function(){
    			this.$emit('update:sku-table-data',this.skuTableData);
    		}
        },
	methods:{
		onBatchSet:function(type){
			var extRowKey = this.attrConfig['price_count_type']==<?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>?'item':'weight';
			for(var i in this.skuTableData)
			{
				if(type == 'washPrice')
				{
					this.skuTableData[i][extRowKey]['wash_price'] = this.batchWashPrice;
				}
			}
			this.showBatchWashPrice = false;
		},
		skuExtRowProp:function(){
			return {
				'item':{
					'wash_price':0
				},
				'weight':{
					'wash_price':0
				}
			};
		},
		setFormData:function(data){
			if(!data)
				return;
			var _this = this;
			this.$nextTick(function(){
				for(var i in data)
				{
					var row = _this.skuTableData.find(function(item){
						return item['sku_id'] == data[i]['sku_id'];
					});
					if(row)
					{
						var extRowKey = _this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>?'item':'weight';
						row[extRowKey]['wash_price'] = data[i]['wash_price'];
					}
				}
			});
		},
		getFormData:function(){
			var form = [];
			for(var i in this.skuTableData)
			{
				var washPrice;
				if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>)
				{
					washPrice = this.skuTableData[i]['item']['wash_price'];
				}else if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>){
					washPrice = this.skuTableData[i]['weight']['wash_price'];
				}
				form.push({
					sku_id:this.skuTableData[i]['sku_id'],
					wash_price:washPrice
				});
			}
			return form;
		}
	}
});

Vue.component('goods-sku-detail',{
	props:{
		attrAssignData:{},
		sysAttrWeightValue:{},
		sysAttrHandleValue:{},
		attrConfig:{},
		skuPriceTableData:{},
		skuSysAttrHandleTableData:{},
		skuSysAttrWashTableData:{},
		skuSysAttrWeightTableData:{}
	},
	watch:{
		attrAssignData:{
			handler:function(){
				this.setCompleteAssignData();
			},
			immediate:true
		},
		sysAttrWeightValue:{
			handler:function(){
				this.setCompleteAssignData();
			},
			immediate:true
		},
		sysAttrHandleValue:{
			handler:function(){
				this.setCompleteAssignData();
			},
			immediate:true
		},
		attrConfig:{
			handler:function(){
				this.setCompleteAssignData();
			},
			deep:true,
			immediate:true
		},
		skuTableData:function(){
			this.$emit('update:sku-table-data', this.skuTableData);
		}
	},
	computed:{
		costPrice:function(){
			var _this = this
			return function(row){
				return _this.getCostPrice(row)
			}
        },
        salePrice:function(){
        	var _this = this
			return function(row){
				return _this.getSalePrice(row)
			}
        }
	},
	template:`<div class='goods-sys-attr-container'>
		<div class="goods-sys-attr-title-block"><span class="blockquote-title">规格明细</span></div>
		<goods-sku-table :attr-assign="completeAssignData" :table-data.sync="skuTableData" :ext-row-props="skuExtRowProp">
    		<template slot="columns">
    		<el-table-column width="200px" label="成本价">
        		<template slot-scope="scope">
					￥{{costPrice(scope.row).toFixed(2)}}
        		</template>
            </el-table-column>
    		<el-table-column width="200px" label="销售价">
        		<template slot-scope="scope">
        			￥{{salePrice(scope.row).toFixed(2)}}
        		</template>
            </el-table-column>
    		<el-table-column label="库存">
        		<template slot-scope="scope">
    				<el-input-number v-model="scope.row['stock']" :min="0" :max="10000000" size="small"></el-input-number>
        		</template>
            </el-table-column>
            </template>
		<div class="goods-sku-table-bottom" slot="bottom"></div>
            </goods-sku-table>
        </div></div>`,
    data:function(){
		return {
			skuTableData:[],
			completeAssignData:[]
		};
    },
    methods:{
    	getCostPrice:function(row){
        	_this = this;
			if(_this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM?>)
			{
				if(row['priceSkuRow'])
				{
					return parseFloat(row.priceSkuRow['item']['cost']);
				}else{
					return parseFloat(_this.attrConfig['item']['cost']);
				}
			}else if(_this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT?>)
			{
				if(row['weightSkuRow'])
				{
					if(row['priceSkuRow'])
					{
						return getWeightCostPrice(parseFloat(row['weightSkuRow']['common']['weight']),parseFloat(row['priceSkuRow']['weight']['cost']));
					}else{
						return getWeightCostPrice(parseFloat(row['weightSkuRow']['common']['weight']),parseFloat(_this.attrConfig['weight']['cost']));
					}
				}
			}
			return 0;
			
		},
        getSalePrice:function(row){
        	_this = this;
			var salePrice = 0;
			if(_this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM?>)
			{
				if(row['priceSkuRow'])
				{
					if(_this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE?>)
					{
						salePrice += getSalePrice(parseFloat(row.priceSkuRow['item']['cost']),parseFloat(row.priceSkuRow['item']['sale_scale']));
					}else{
						salePrice += parseFloat(row.priceSkuRow['item']['sale_price']);
					}
				}else{
					if(_this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE?>)
					{
						salePrice += getSalePrice(parseFloat(_this.attrConfig['item']['cost']),parseFloat(_this.attrConfig['item']['sale_scale']));
					}else{
						salePrice += parseFloat(_this.attrConfig['item']['sale_price']);
					}
				}
				if(row['handleSkuRow'] && this.attrConfig['sys_attr_handle'] == <?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE?>)
				{
					salePrice += parseFloat(row['handleSkuRow']['item']['handle_price']);
				}
				if(row['needWash'])
				{
					if(row['washSkuRow'])
					{
						salePrice += parseFloat(row['washSkuRow']['item']['wash_price']);
					}else{
						salePrice += parseFloat(_this.attrConfig['item']['wash_price']);
					}
				}
			}else if(_this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT?>)
			{
				if(row['priceSkuRow'])
				{
					if(_this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE?>)
					{
						salePrice += getWeightSalePrice(parseFloat(row['priceSkuRow']['weight']['cost']),parseFloat(row['weightSkuRow']['common']['weight']),parseFloat(row['priceSkuRow']['weight']['sale_scale']));
					}else{
						salePrice += parseFloat(row.weightSkuRow['sale_price']);
					}
				}else{
					if(_this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE?>)
					{
						salePrice += getWeightSalePrice(parseFloat(_this.attrConfig['weight']['cost']),parseFloat(row['weightSkuRow']['common']['weight']),parseFloat(_this.attrConfig['weight']['sale_scale']));
					}else{
						salePrice += parseFloat(row.weightSkuRow['sale_price']);
					}
				}
				if(row['handleSkuRow'] && this.attrConfig['sys_attr_handle'] == <?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE?>)
				{
					salePrice += parseFloat(row['handleSkuRow']['weight']['handle_price']) / 500 * parseFloat(row['weightSkuRow']['common']['weight']);
				}
				if(row['needWash'])
				{
					if(row['washSkuRow'])
					{
						salePrice += parseFloat(row['washSkuRow']['weight']['wash_price']) / 500 * parseFloat(row['weightSkuRow']['common']['weight']);
					}else{
						salePrice += parseFloat(_this.attrConfig['weight']['wash_price']) / 500 * parseFloat(row['weightSkuRow']['common']['weight']);;
					}
				}
			}
			return salePrice;
		},
    	skuExtRowProp:function(row){
    		var priceSkuIdArr = [];
    		var weightSkuIdArr = [];
    		var handleSkuIdItem = null;
    		var weightSkuIdItem = null;
    		var needWash = false;
        	var skuIdArr = row['sku_id'].split(':');
        	for(var i in skuIdArr)
        	{
        		skuItemArr = skuIdArr[i].split('_');
        		var item = this.attrAssignData.find(function(item){
					return item['attr']['value'] == skuItemArr[0];
                });
                if(item)
                {
                	priceSkuIdArr.push(skuItemArr[0]+'_'+skuItemArr[1]);
                }
                if(skuItemArr[0] == <?=GoodsAttr::SYSTEM_HANDLE_ID?>)
                {
                	handleSkuIdItem = skuItemArr[0] +'_'+ skuItemArr[1];
                }
                else if(skuItemArr[0] == <?=GoodsAttr::SYSTEM_WEIGHT_ID?>)
                {
                	weightSkuIdItem = skuItemArr[0] +'_'+ skuItemArr[1];
                }
                else if(skuItemArr[0] == <?=GoodsAttr::SYSTEM_WASH_ID?> && skuItemArr[1] == <?=GoodsAttrItem::NEED_WASH_YES_ID?>)
                {
                	needWash = true;
                }
            }
        	priceSkuId = priceSkuIdArr.join(':');
        	var priceSkuRow = null;
        	var item = this.skuPriceTableData.find(function(item){
				return item['sku_id'] == priceSkuId;
            });
        	priceSkuRow = item;

        	weightSkuRow = null;
        	if(weightSkuIdItem)
        	{
            	var tPriceSkuIdArr = priceSkuIdArr.concat();
            	tPriceSkuIdArr.push(weightSkuIdItem);
            	weightSkuId = tPriceSkuIdArr.join(':');
            	var item = this.skuSysAttrWeightTableData.find(function(item){
    				return item['sku_id'] == weightSkuId;
                });
            	weightSkuRow = item;
            }
        	
        	handleSkuRow = null;
        	if(handleSkuIdItem)
        	{
        		var tPriceSkuIdArr = priceSkuIdArr.concat();
        		tPriceSkuIdArr.push(handleSkuIdItem);
            	handleSkuId = tPriceSkuIdArr.join(':');
            	var item = this.skuSysAttrHandleTableData.find(function(item){
    				return item['sku_id'] == handleSkuId;
                });
            	handleSkuRow = item;
            }
        	var washSkuRow = null;
        	if(needWash)
        	{
            	var item = this.skuSysAttrWashTableData.find(function(item){
    				return item['sku_id'] == priceSkuId;
                });
            	washSkuRow = item;
            }
			return {
				priceSkuRow:priceSkuRow,
				weightSkuRow:weightSkuRow,
				handleSkuRow:handleSkuRow,
				washSkuRow:washSkuRow,
				needWash:needWash,
				stock:10000000
			};
		},
    	setCompleteAssignData:function(){
			if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>)
			{
				if(this.sysAttrWeightValue.length<=0)
				{
					this.completeAssignData = [];
					return;
				}
			}
			var completeAssignData = [];
			completeAssignData = completeAssignData.concat(this.attrAssignData);
			if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?> && this.sysAttrWeightValue.length>0)
			{
				var item = [];
				for(var i in this.sysAttrWeightValue)
				{
					item.push({
						attrValue:'<?=GoodsAttr::SYSTEM_WEIGHT_ID?>',
						value:this.sysAttrWeightValue[i]['value'],
						label:this.sysAttrWeightValue[i]['label'],
						common:{
							weight:this.sysAttrWeightValue[i]['weight']
						}
					});
				}
				completeAssignData.push({
					attr:{
						label:'重量',
						value:'<?=GoodsAttr::SYSTEM_WEIGHT_ID?>',
					},
					item:item
				});
			}
			if(this.attrConfig['sys_attr_handle'] == <?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE ?> && this.sysAttrHandleValue.length>0)
			{
				var item = [];
				/*item.push({
					attrValue:'<?=GoodsAttr::SYSTEM_HANDLE_ID?>',
					value:<?=GoodsAttrItem::HANDLE_NOT_ID?>,
					label:'不加工'
				});*/
				for(var i in this.sysAttrHandleValue)
				{
					item.push({
						attrValue:'<?=GoodsAttr::SYSTEM_HANDLE_ID?>',
						value:this.sysAttrHandleValue[i]['value'],
						label:this.sysAttrHandleValue[i]['label']
					});
				}
				completeAssignData.push({
					attr:{
						label:'加工方式',
						value:'<?=GoodsAttr::SYSTEM_HANDLE_ID?>',
					},
					item:item
				});
			}
			if(this.attrConfig['sys_attr_wash'] == <?=GoodsAttrConfig::SYS_ATTR_WASH_ENABLE ?>)
			{
				completeAssignData.push({
					attr:{
						label:'是否清洗',
						value:'<?=GoodsAttr::SYSTEM_WASH_ID?>',
					},
					item:[
						{
							label:'无需清洗',
							value:<?=GoodsAttrItem::NEED_WASH_NOT_ID?>,
							attrValue:'<?=GoodsAttr::SYSTEM_WASH_ID?>'
						},
						{
							label:'需要清洗',
							value:<?=GoodsAttrItem::NEED_WASH_YES_ID?>,
							attrValue:'<?=GoodsAttr::SYSTEM_WASH_ID?>'
						}
					]
				});
			}
			this.completeAssignData = completeAssignData;
        },
        getFormData:function(){
			var form = [];
			for(var i in this.skuTableData)
			{
				form.push({
					sku_id:this.skuTableData[i]['sku_id'],
					cost:this.getCostPrice(this.skuTableData[i]),
					sale_price:this.getSalePrice(this.skuTableData[i]),
					stock:this.skuTableData[i]['stock']
				});
			}
			return form;
		},
		setFormData:function(data){
			if(!data)
				return;
			var _this = this;
			this.$nextTick(function(){
				for(var i in data)
				{
					var row = _this.skuTableData.find(function(item){
						return item['sku_id'] == data[i]['sku_id'];
					});
					if(row)
					{
						row['stock'] = data[i]['stock'];
					}
				}
			});
		}
    }
});
</script>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<form-content ref="form" :model="form" label-width="75px" :request-init-loading.sync="requestInitLoading">
				<div class="form-wrap">
					<div class="blockquote"><span class="blockquote-title">基本信息</span></div>
					<el-form-item label="类型">
        			    <div class="form-item-inner-text">{{typeName}}</div>
        			 </el-form-item>
    				 <el-form-item label="商品名">
        			    <el-input v-model="form.name"></el-input>
        			 </el-form-item>
        			 <el-form-item label="修饰词">
        			    <el-input v-model="form.adorn_text"></el-input>
        			    <div class="form-item-inner-text">有赞用户端呈现，分控打印不做显示</div>
        			 </el-form-item>
        			 <el-form-item label="内部分类">
        			    <el-cascader v-model="form.categorys" :clearable=true :options="categoryNodes" :props="{ expandTrigger: 'click' }" ref="category"></el-cascader>
        			 </el-form-item>
        			 <el-form-item label="有赞分组">
        			   <el-select v-model="form.tags" :collapse-tags="true"  :multiple="true">
                        <el-option v-for="option in tagOptions" :label="option.label" :value="option.value" :key="option.value">
                       </el-option>
                      </el-select>
        			 </el-form-item>
        			 <el-form-item label="商品图">
        			    <image-upload-box size="800x800" :limit="15" v-model="form.images" ref="imageUploadBox" @show="onShowUploadImage" :dir-id="uploadDirId" :loading="requestInitLoading" :url-value="false" :upload-file-name="form.name"></image-upload-box>
        			 </el-form-item>
        			 <el-form-item label="商品卖点">
        			    <el-input v-model="form.youzan_sell_point"></el-input>
        			 </el-form-item>
        			 <el-form-item label="划线价">
        			 	<el-input v-model="form.youzan_origin_price"></el-input>
        			 </el-form-item>
        			  <el-form-item>
        			    <el-checkbox :true-label="1" :false-label="0" v-model="form.youzan_join_level_discount">是否参加会员折扣</el-checkbox>
        			 </el-form-item>
        			 <div class="blockquote"><span class="blockquote-title">规格配置</span>
        			 <el-checkbox :true-label="<?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE ?>" :false-label="<?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_FALSE ?>" v-model="attrConfig.auto_create_sale_price">自动生成售价</el-checkbox></div>
        			 <el-form-item label="计价方式">
        			      <el-radio-group v-model="attrConfig.price_count_type">
                            <el-radio :label="<?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>" @change="attrConfig.sys_attr_weight = <?=GoodsAttrConfig::SYS_ATTR_WEIGHT_DISABLE ?>">按件计价</el-radio>
                            <el-radio :label="<?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>" @change="attrConfig.sys_attr_weight = <?=GoodsAttrConfig::SYS_ATTR_WEIGHT_ENABLE ?> ">按斤计价</el-radio>
                          </el-radio-group>
        			 </el-form-item>
			 	 	 <el-form-item label="1级规格">
			 	 	 	<div class="form-item-inner-text">（以品质、体量区分）</div>
			 	 	 	<goods-attr-assign :assign-data.sync="attrAssignData" ref="goodsAttrAssign"
			 	 	 		:attr-options-url="attrOptionsUrl" 
			 	 	 		:attr-fast-create-url="attrFastCreateUrl"
			 	 	 		:item-options-url="itemOptionsUrl"
			 	 	 		:item-fast-create-url="itemFastCreateUrl">
        			 </el-form-item>
        			  <el-form-item v-show="hasCustomAttr">
        			  	  <set-sku-price :sku-table-data.sync="skuPriceTableData" ref="setSkuPrice" :attr-config="attrConfig" :attr-assign-data="attrAssignData"></set-sku-price>
        			 </el-form-item>
        			 <el-form-item v-show="!hasCustomAttr" label="库存">
    			  		<el-input-number v-model="attrConfig.stock" :precision="0" :min="0" :max="10000000" size="small"></el-input-number>
    			  	</el-form-item>
        			 <div v-show="attrConfig.price_count_type == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?> && !hasCustomAttr">
            			  	<el-form-item class="cb-weight" label="成本价">
            			  		<el-input-number v-model="attrConfig.item.cost" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>&nbsp;&nbsp;/件
            			  	</el-form-item>
            			  	<el-form-item label="售价比">
            			  		<el-input-number :disabled="!attrConfig.auto_create_sale_price" v-model="attrConfig.item.sale_scale" :min="0.01" :precision="2" :step="0.01" size="small"></el-input-number>
            			  	</el-form-item>
            			  	<el-form-item label="售价">
            			  		<div v-if="attrConfig.auto_create_sale_price" class="form-item-inner-text">￥{{getSalePrice(attrConfig.item.cost,attrConfig.item.sale_scale).toFixed(2)}}</div>
            			  		<el-input-number v-else v-model="attrConfig.item.sale_price" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
            			  	</el-form-item>
        			 </div>
        			 <div v-show="attrConfig.price_count_type == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?> && !hasCustomAttr">
            			  	<el-form-item label="成本价">
            			  		<el-input-number v-model="attrConfig.weight.cost" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>&nbsp;&nbsp;/斤
            			  	</el-form-item>
            			  	<el-form-item v-show="!hasCustomAttr" label="售价比">
            			  		<el-input-number :disabled="!attrConfig.auto_create_sale_price" v-model="attrConfig.weight.sale_scale" :min="0.01" :precision="2" :step="0.01" size="small"></el-input-number>
            			  	</el-form-item>
        			 </div>
        			 <el-form-item label="2级规格">
    			  		<div class="goods-sys-attr-checkbox-wrap">
    			  			<el-checkbox :disabled="true" v-model="attrConfig.sys_attr_weight" :true-label="<?=GoodsAttrConfig::SYS_ATTR_WEIGHT_ENABLE ?>" :false-label="<?=GoodsAttrConfig::SYS_ATTR_WEIGHT_DISABLE ?>">重量</el-checkbox>
    			  			<el-checkbox v-model="attrConfig.sys_attr_handle" :true-label="<?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE ?>" :false-label="<?=GoodsAttrConfig::SYS_ATTR_HANDLE_DISABLE ?>">加工方式</el-checkbox>
    			  			<!-- <el-checkbox v-model="attrConfig.sys_attr_wash" :true-label="<?=GoodsAttrConfig::SYS_ATTR_WASH_ENABLE ?>" :false-label="<?=GoodsAttrConfig::SYS_ATTR_WASH_DISABLE ?>">清洗</el-checkbox> -->
    			  		</div>
    			  		<set-sys-attr-weight ref="setSysAttrWeight" v-show="attrConfig.sys_attr_weight == <?=GoodsAttrConfig::SYS_ATTR_WEIGHT_ENABLE ?>"
    			  			 :sku-table-data.sync="skuSysAttrWeightTableData"
    			  			 :sku-price-table-data="skuPriceTableData"
    			  			 :weight-value.sync="sysAttrWeightValue"
    			  			 :attr-config="attrConfig"
    			  			 :attr-assign-data="attrAssignData"
    			  			 :item-fast-create-url="itemFastCreateUrl"
    			  			 :system-attr-id="<?=GoodsAttr::SYSTEM_WEIGHT_ID ?>">
    			  		</set-sys-attr-weight>
    			  		<set-sys-attr-handle ref="setSysAttrHandle" v-show="attrConfig.sys_attr_handle == <?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE ?>"
    			  			 :sku-table-data.sync="skuSysAttrHandleTableData"
    			  			 :attr-config="attrConfig"
    			  			 :handle-value.sync="sysAttrHandleValue"
    			  			 :attr-assign-data="attrAssignData"
    			  			 :item-fast-create-url="itemFastCreateUrl"
    			  			 :item-options-url="itemOptionsUrl"
    			  			 :system-attr-id="<?=GoodsAttr::SYSTEM_HANDLE_ID ?>">
    			  		</set-sys-attr-handle>
    			  		<set-sys-attr-wash ref="setSysAttrWash" v-show="attrConfig.sys_attr_wash == <?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE ?>"
    			  			 :sku-table-data.sync="skuSysAttrWashTableData"
    			  			 :attr-config="attrConfig"
    			  			 :attr-assign-data="attrAssignData">
    			  		</set-sys-attr-wash>
    			  		<goods-sku-detail 
    			  			ref="goodsSkuDetail"
    			  			v-show="detailSkuTableData.length>0"
    			  			:sku-table-data.sync="detailSkuTableData"
    			  			:attr-config="attrConfig"
        			  		:attr-assign-data="attrAssignData" 
        			  		:sys-attr-weight-value="sysAttrWeightValue"
        			  		:sys-attr-handle-value="sysAttrHandleValue"
        			  		:sku-price-table-data="skuPriceTableData"
        			  		:sku-sys-attr-handle-table-data="skuSysAttrHandleTableData"
        			  		:sku-sys-attr-wash-table-data="skuSysAttrWashTableData"
        			  		:sku-sys-attr-weight-table-data="skuSysAttrWeightTableData"
        			  		></goods-sku-detail>
    			  	</el-form-item>
        			 <div class="blockquote"><span class="blockquote-title">其他信息</span></div>
        			 <el-form-item label="状态">
        			   <el-select v-model="form.state">
                        <el-option v-for="option in stateOptions" :label="option.label" :value="option.value" :key="option.value">
                       </el-option>
                      </el-select>
        			 </el-form-item>
        			 <el-form-item v-if="info.created_time" label="创建时间">
        			   <div class="form-item-inner-text">{{info.created_time}}</div>
        			 </el-form-item>
        			 <div class="blockquote"><span class="blockquote-title">打印配置</span></div>
					<el-form-item label="打印拼接">
        			    <el-input v-model="form.print_config" type="textarea">
                            </el-input><div class="form-item-inner-text">分控打印端呈现，有赞用户端不做显示（支持回车换行）</div>
        			 </el-form-item>
        			 <div class="blockquote"><span class="blockquote-title">有赞同步</span></div>
        			 <el-form-item label="有赞ID">
        			    <el-input v-model="form.youzan_id"></el-input>
        			    <div><el-link target="_blank" :href="'https://www.youzan.com/v2/showcase/goods/edit#/id='+form.youzan_id" type="primary">有赞链接</el-link></div>
        			 </el-form-item>
        			 
    				<el-form-item v-if="info && info.youzan_syn_state" label="本地版本">
    					<div class="form-item-inner-text">{{info.version}}</div>
        			 </el-form-item>
        			 <el-form-item v-if="info && info.youzan_version" label="有赞版本">
    					<div class="form-item-inner-text">{{info.youzan_version}}</div>
        			 </el-form-item>
        			 <el-form-item v-if="info && info.youzan_syn_time" label="同步时间">
    					<div class="form-item-inner-text">{{info.youzan_syn_time}}</div>
        			 </el-form-item>
        			 <el-form-item v-if="info && info.youzan_syn_state" label="同步状态">
    					<div class="form-item-inner-text">{{info.youzan_syn_state}}</div>
        			 </el-form-item>
              	</div>
              	<el-button-group slot="toolbar">
                  <el-button size="small" type="primary" native-type="submit" @click="onSubmit()" :loading="saveLoading">保存</el-button>
                </el-button-group>
			</form-content>
		<?=$this->endContent() ?>
	</div>
</body>
<script>

function getSalePrice(cost,saleScale){
	return cost*saleScale;
}

function getWeightSalePrice(cost,weightNum,saleScale){
	return cost/500*weightNum*saleScale;
}

function getWeightCostPrice(cost,weightNum){
	return cost/500*weightNum;
}

Vue.$page.mixin.push({
	data:function(){
		var _this = this;
		var data = {
			saveUrl:'<?=Url::toRoute(['','id'=>$model['id'],'type'=>!$model?$type:null]) ?>',
			initDataUrl:'<?=Url::toRoute(['save-form-data','id'=>$model['id'],'type'=>$type]) ?>',
			saveLoading:false,
			attrFastCreateUrl:'<?=Url::toRoute(['/goods/attr/fast-create','type'=>$type]) ?>',
			attrOptionsUrl:'<?=Url::toRoute(['/goods/attr/get-options','type'=>$type]) ?>',
			itemFastCreateUrl:'<?=Url::toRoute(['/goods/attr-item/fast-create']) ?>',
			itemOptionsUrl:'<?=Url::toRoute(['/goods/attr-item/get-options']) ?>',
			form:<?=HtmlHelper::encodeJson($saveForm) ?>,
			info:<?=HtmlHelper::encodeJson($info) ?>,
			uploadDirId:'',
			requestInitLoading:false,
			attrConfig:{
				auto_create_sale_price:<?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_FALSE ?>,
				price_count_type:<?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>,
				sys_attr_weight:<?=GoodsAttrConfig::SYS_ATTR_WEIGHT_DISABLE ?>,
				sys_attr_handle:<?=GoodsAttrConfig::SYS_ATTR_WEIGHT_DISABLE ?>,
				sys_attr_wash:<?=GoodsAttrConfig::SYS_ATTR_WEIGHT_DISABLE ?>,
				stock:10000000,
				item:{
					cost:0,
					sale_scale:1,
					sale_price:0,
					wash_price:0
				},
				weight:{
					cost:0,
					sale_scale:1,
					wash_price:0
				}
			},
			attrAssignData:[],
			sysAttrWeightValue:[],
			sysAttrHandleValue:[],
			detailSkuTableData:[],
			typeName:'<?=Goods::$typeMap[$type] ?>',
			categoryNodes:[],
			tagOptions:[],
			stateOptions:<?=HtmlHelper::encodeMapOptions(Goods::$stateMap) ?>,
			skuPriceTableData:[],
			skuSysAttrHandleTableData:[],
			skuSysAttrWashTableData:[],
			skuSysAttrWeightTableData:[]
		}
		return data;
	},
	computed:{
		hasCustomAttr:function(){
			return this.attrAssignData.length>0;
		},
		hasSysAttrWeight:function(){
			return this.sysAttrWeightValue.length>0;
		},
		hasSysHandleWeight:function(){
			return this.sysAttrHandleValue.length>0;
		}
	},
	mounted:function(){
		var _this = this;
		this.$refs['form'].requestInitData(this.initDataUrl,{
			success:function(response){
				_this.categoryNodes = response.data.categoryNodes;
				_this.tagOptions = response.data.tagOptions;
				_this.setAttrConfigFormData(response.data.attrConfigForm);
				_this.$refs['goodsAttrAssign'].setFormData(response.data.attrAssignForm);
				_this.$refs['setSkuPrice'].setFormData(response.data.skuPriceForm);
				_this.$refs['setSysAttrWeight'].setFormData(response.data.sysAttrWeightForm);
				_this.$refs['setSysAttrHandle'].setFormData(response.data.sysAttrHandleForm);
				_this.$refs['setSysAttrWash'].setFormData(response.data.sysAttrWashForm);
				_this.$refs['goodsSkuDetail'].setFormData(response.data.skuDetailForm)
			}
		});
	},
	methods:{
		getSalePrice:getSalePrice,
		getWeightSalePrice:getWeightSalePrice,
		onSubmit:function(){
			var _this = this;
			if(this.form['images'].length<=0)
			{
				this.$refs['form'].setErrorMessage('至少上传一张商品图片');
				return;
			}
			if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?> && !this.hasSysAttrWeight)
			{
				this.$refs['form'].setErrorMessage('按斤计价方式需要为商品添加重量规格');
				return;
			}
			var attrCount = 0;
			var goodsAttrAssign = this.$refs['goodsAttrAssign'].getFormData()
			attrCount += goodsAttrAssign.length;
			if(this.attrConfig['sys_attr_weight'] == <?=GoodsAttrConfig::SYS_ATTR_WEIGHT_ENABLE ?>)
			{
				var sysAttrWeight = this.$refs['setSysAttrWeight'].getFormData();
				if(sysAttrWeight.assign.length>0)
					attrCount += 1;
			}
			if(this.attrConfig['sys_attr_handle'] == <?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE ?>)
			{
				var sysAttrHandle = this.$refs['setSysAttrHandle'].getFormData();
				if(sysAttrHandle.assign.length>0)
					attrCount += 1;
			}
			if(attrCount>3)
			{
				this.$refs['form'].setErrorMessage('规格属性最多只能设置3个');
				return;
			}
			
			this.$refs['form'].submit(this.saveUrl,{
				before:function(data){
					data['attr_config'] = _this.getAttrConfigFormData();
					data['attr_assign'] = _this.$refs['goodsAttrAssign'].getFormData();
					data['sku_price'] = _this.$refs['setSkuPrice'].getFormData();
					if(_this.attrConfig['sys_attr_weight'] == <?=GoodsAttrConfig::SYS_ATTR_WEIGHT_ENABLE ?>)
					{
						data['sys_attr_weight'] = _this.$refs['setSysAttrWeight'].getFormData();
					}
					if(_this.attrConfig['sys_attr_handle'] == <?=GoodsAttrConfig::SYS_ATTR_HANDLE_ENABLE ?>)
					{
						data['sys_attr_handle'] = _this.$refs['setSysAttrHandle'].getFormData();
					}
					if(_this.attrConfig['sys_attr_wash'] == <?=GoodsAttrConfig::SYS_ATTR_WASH_ENABLE ?>)
					{
						data['sys_attr_wash'] = _this.$refs['setSysAttrWash'].getFormData();
					}
					data['sku_detail'] = _this.$refs['goodsSkuDetail'].getFormData();
					_this.saveLoading = true;
				},
				complete:function(){
					_this.saveLoading = false;
				}
			});
		},
		onShowUploadImage:function(){
			var categorys = this.$refs['category'].getCheckedNodes();
			var nodeId = ''
			if(categorys.length>0 && categorys[0] && categorys[0]['data']['upload_dir_id'])
			{
				nodeId = categorys[0]['data']['upload_dir_id'];
			}
			this.uploadDirId = nodeId;
		},
		setAttrConfigFormData:function(attrConfigForm){
			if(attrConfigForm)
			{
				this.attrConfig['auto_create_sale_price'] = attrConfigForm['auto_create_sale_price'];
				this.attrConfig['price_count_type'] = attrConfigForm['price_count_type'];
				this.attrConfig['sys_attr_weight'] = attrConfigForm['sys_attr_weight'];
				this.attrConfig['sys_attr_handle'] = attrConfigForm['sys_attr_handle'];
				this.attrConfig['sys_attr_wash'] = attrConfigForm['sys_attr_wash'];
				this.attrConfig['stock'] = attrConfigForm['stock'];
				var priceCountAttrs;
				if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>)
				{
					priceCountAttrs = this.attrConfig['item'];
				}else if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>)
				{
					priceCountAttrs = this.attrConfig['weight'];
				}
				priceCountAttrs['cost'] = attrConfigForm['cost']?attrConfigForm['cost']:priceCountAttrs['cost'];
				priceCountAttrs['wash_price'] = attrConfigForm['wash_price'];
				if(this.attrConfig['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE ?>)
				{
					priceCountAttrs['sale_scale'] = attrConfigForm['sale_scale']?attrConfigForm['sale_scale']:priceCountAttrs['sale_scale'];
				}else{
					priceCountAttrs['sale_price'] = attrConfigForm['sale_price']?attrConfigForm['sale_price']:priceCountAttrs['sale_price'];
				}
			}
		},
		getAttrConfigFormData:function(){
			var attrConfigForm = this.attrConfigForm?this.attrConfigForm:{};
			attrConfigForm['auto_create_sale_price'] = this.attrConfig['auto_create_sale_price'];
			attrConfigForm['price_count_type'] = this.attrConfig['price_count_type'];
			attrConfigForm['sys_attr_weight'] = this.hasSysAttrWeight?this.attrConfig['sys_attr_weight']:<?=GoodsAttrConfig::SYS_ATTR_WEIGHT_DISABLE ?>;
			attrConfigForm['sys_attr_handle'] = this.hasSysHandleWeight?this.attrConfig['sys_attr_handle']:<?=GoodsAttrConfig::SYS_ATTR_HANDLE_DISABLE ?>;
			attrConfigForm['sys_attr_wash'] = this.attrConfig['sys_attr_wash'];
			attrConfigForm['has_custom_attr']  = this.hasCustomAttr?<?=GoodsAttrConfig::HAS_CUSTOM_ATTR_TRUE ?>:<?=GoodsAttrConfig::HAS_CUSTOM_ATTR_FALSE ?>;
			attrConfigForm['stock'] = this.attrConfig['stock'];
			if(this.hasCustomAttr)
			{
				attrConfigForm['sale_price'] = null;
				attrConfigForm['cost'] = null;
				attrConfigForm['sale_scale'] = null;
				attrConfigForm['wash_price'] = null;
				attrConfigForm['stock'] = null;
			}else{
				if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_ITEM ?>)
				{
					attrConfigForm['sale_price'] = this.attrConfig['item']['sale_price'];
					attrConfigForm['cost'] = this.attrConfig['item']['cost'];
					attrConfigForm['wash_price'] = !this.hasCustomAttr && this.attrConfig['sys_attr_wash'] == <?=GoodsAttrConfig::SYS_ATTR_WASH_ENABLE?>?this.attrConfig['item']['wash_price']:null;
					if(attrConfigForm['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE ?>)
					{
						attrConfigForm['sale_scale'] = this.attrConfig['item']['sale_scale'];
						attrConfigForm['sale_price'] = this.getSalePrice(this.attrConfig['item']['cost'],this.attrConfig['item']['sale_scale']);
					}else{
						attrConfigForm['sale_scale'] = null;
					}
				}else if(this.attrConfig['price_count_type'] == <?=GoodsAttrConfig::PRICE_COUNT_TYPE_WEIGHT ?>)
				{
					attrConfigForm['sale_price'] = null;
					attrConfigForm['cost'] = this.attrConfig['weight']['cost'];
					attrConfigForm['wash_price'] = !this.hasCustomAttr && this.attrConfig['sys_attr_wash'] == <?=GoodsAttrConfig::SYS_ATTR_WASH_ENABLE?>?this.attrConfig['weight']['wash_price']:null;
					if(attrConfigForm['auto_create_sale_price'] == <?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_TRUE ?>)
					{
						attrConfigForm['sale_scale'] = this.attrConfig['weight']['sale_scale'];
					}else{
						attrConfigForm['sale_scale'] = null;
					}
				}
			}
			return attrConfigForm;
		}
	}
});
</script>