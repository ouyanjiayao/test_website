<?php
use common\librarys\Url;
use common\librarys\HtmlHelper;
use common\models\Goods;
use common\models\GoodsAttrConfig;
use common\models\GoodsAttr;

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
.goods-dp-config-pop{
	padding: 20px;
}
</style>
<script>
Vue.component('goods-dp-config',{
	props:{
		showDpConfigDialog:{
			default:function(){
				return false;
			}
		}
	},
	watch:{
		showDpConfigDialog:function(newValue){
			this.$emit('update:show-dp-config-dialog', newValue);
		}
	},
	template:`
		<el-dialog @open="showGoodsDpConfig = true" @closed="showGoodsDpConfig = false;" :append-to-body="true" title="单品配置" :visible.sync="showDpConfigDialog" width="1000px" custom-class="form-dialog">
		<data-girdview v-if="showGoodsDpConfig" :each-row="eachRow" :select-rows.sync="selectRows" row-key="id" ref="dataGirdview" :url="listUrl" :search-form="searchForm" :request-params="listRequestParams">
    	  	<template slot="searchForm">
            <el-form-item label="关键词">
    			<el-input v-model="searchForm.model.keyword"></el-input>
    	    </el-form-item>
      	</template>
      	<template slot="toolbar">
      		<el-button-group>
    		</el-button-group>
      	</template>
      	<el-table-column prop="id" label="ID" min-width="30%"></el-table-column>
      	<el-table-column label="商品" width="80px">
        	<template v-if="scope.row.first_image" slot-scope="scope">
        	<el-image class="girdview-cell-img" :src="scope.row.first_image" fit="cover"></el-image>
        	</template>
        </el-table-column>
        <el-table-column prop="name" min-width="70%"></el-table-column>
        <el-table-column width="250" align="right">
        	<template slot-scope="scope">
        		<el-button-group>
        		<template v-if="scope.row['attr_assign'].length>0">
            		<el-popover v-model="scope.row['show_select_pop']" placement="bottom" trigger="click" popper-class="goods-dp-config-pop">
                    		<el-form label-position="top" style="min-width:335px;">
                        		<el-form-item v-for="attrAssign in scope.row['attr_assign']" v-key="attrAssign['id']" :label="attrAssign['name']">
                            		<el-radio-group v-model="scope.row['selectAttrValues'][attrAssign['id']]" size="small">
                            	      <el-radio-button v-for="item in attrAssign['items']" v-key="item['id']" :label="item['id']">{{item['name']}}</el-radio-button>
                            	    </el-radio-group>
                            	  </el-form-item>
                    		</el-form>
                <el-button style="float:right" type="primary" size="small" @click="confirmSelect(scope.row)" :disabled="disableSelect(scope.row)">确定</el-button>
                		</div>
                		<el-button slot="reference" size="small">选择</el-button>
                	</el-popover>
        		</template>
		<template v-else>
		<el-button size="small" @click="confirmSelect(scope.row)">选择</el-button>
		</template>
            	</el-button-group>
        	</template>
        </el-table-column>
      </data-girdview>
		</el-dialog>
	      `,
	data:function(){
		var data = {
			selectRows:[],
			listUrl:'<?=Yii::$app->user->checkAccess('dp-config-list-data')?Url::toRoute('dp-config-list-data'):'' ?>',
			initSearchFormUrl:'<?=Url::toRoute('search-form-data') ?>',
			searchForm:{
    			default:function(){
    				return {
    					'keyword':'',
    					'state':'',
    					'category_id':''
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
    		stateOptions:<?=HtmlHelper::encodeMapOptions(Goods::$stateMap) ?>,
			categoryNodes:[],
			attrRadioValues:[],
			showGoodsDpConfig:false
		};
		data.searchForm.model = data.searchForm.default();
		data.listRequestParams.model = data.listRequestParams.default();
		return data;
	},
	computed:{
		disableSelect:function(){
			return function(row){
				result = false;
				for(var key in row['selectAttrValues'])
				{
					if(!row['selectAttrValues'][key])
					{
						result = true;
					}
				}
				return result;
			}
		}
	},
	methods:{
		eachRow:function(row){
			if(row['attr_assign'].length>0)
			{
				row['selectAttrValues'] = [];
				for(var key in row['attr_assign'])
				{
					row['selectAttrValues'][row['attr_assign'][key]['id']] = '';
				}
			}
			row['show_select_pop'] = false;
			return row;
		},
		confirmSelect:function(row){
			item = {};
			item['id'] = row['id'];
			item['name'] = row['name'];
			item['selectAttrs'] = [];
			item['cost'] = parseFloat(row['attr_config']['cost']);
			item['price_count_type'] = row['attr_config']['price_count_type'];
			row['show_select_pop'] = false;
			if(row['attr_assign'].length>0)
			{
				for(var key in row.attr_assign)
				{
					attrItem = row.attr_assign[key]['items'].find(function(item){
						return item['id'] == row['selectAttrValues'][row.attr_assign[key]['id']]
					});
					item['selectAttrs'].push({
						attr:{
							id:row.attr_assign[key]['id'],
							name:row.attr_assign[key]['name'],
							type:row.attr_assign[key]['type']
						},
						item:{
							id:attrItem['id'],
							name:attrItem['name']
						}
					});
				}
				if(row['sku_price'].length>0)
				{
					var selectAttrs = item['selectAttrs']
					priceSkuId = [];
					for(var key in selectAttrs)
					{
						if(selectAttrs[key]['attr']['type'] == 1)
						{
							priceSkuId.push(selectAttrs[key]['attr']['id']+'_'+selectAttrs[key]['item']['id']);
						}
					}
					priceSkuId = priceSkuId.join('_');
					skuPriceRow = row['sku_price'].find(function(item){
						return item['sku_id'] == priceSkuId;
					});
					item['cost'] = parseFloat(skuPriceRow['cost']);
				}
			}
			this.showDpConfigDialog = false;
			this.$emit('confirm-select', item);
		}
	}
});

Vue.component('goods-sku-detail',{
	props:{
		attrAssignData:{},
		attrConfig:{}
	},	
	computed:{
		dpConfigAttrName:function(){
			return function(dpConfig){
				names = [];
				if(dpConfig['selectAttrs'] && dpConfig['selectAttrs'].length>0)
				{
					for(var key in dpConfig['selectAttrs'])
					{
						names.push(dpConfig['selectAttrs'][key]['item']['name']);
					}
				}
				if(names.length>0)
				{
					return '（'+names.join('，')+'）';
				}else{
					return '';
				}
			}
		},
		cbPrice:function(){
			return function(row){
				var price = 0;
				if(row['dpc'] && row['dpc'].length>0)
				{
					for(var key in row['dpc'])
					{
						if(row['dpc'][key]['price_count_type'] == 1)
						{
							price += row['dpc'][key]['unit'] * row['dpc'][key]['cost'];
						}else if(row['dpc'][key]['price_count_type'] == 2)
						{
							price +=  row['dpc'][key]['cost'] /500 * row['dpc'][key]['unit'];
						}
					}
				}
				return price;
			}
		},
		rowCbPrice:function(){
			return function(dpConfig){
				if(dpConfig['price_count_type'] == 1)
				{
					return dpConfig['unit'] * dpConfig['cost'];
				}else if(dpConfig['price_count_type'] == 2)
				{
					return dpConfig['cost'] /500 * dpConfig['unit'];
				}
			}
		}	
	},
	watch:{
		skuTableData:function(){
			this.$emit('update:sku-table-data', this.skuTableData);
		}
	},
	template:`
		<div><div class='goods-sys-attr-container'>
		<div class="goods-sys-attr-title-block"><span class="blockquote-title">规格明细</span></div>
		<goods-sku-table :attr-assign="attrAssignData" :table-data.sync="skuTableData" :ext-row-props="skuExtRowProp">
			<template slot="columns">
    		<el-table-column width="400px" label="单品配置">
        		<template slot-scope="scope">
				<template v-if="scope.row['dpc']">
				<div v-for="(dpConfig,index) in scope.row['dpc']" v-key="index" style="margin-bottom:10px;border-bottom: 1px solid #f2f2f2;padding-bottom:10px;">
					<div style="margin-bottom:10px;">{{dpConfig['name']}}{{dpConfigAttrName(dpConfig)}}&nbsp;&nbsp;<el-button type="text" @click="removeDpConfig(scope.row,index)">移除</el-button></div>
		<div v-if="dpConfig['price_count_type'] == 1">件数：<el-input-number :precision="0" :min=0 v-model="dpConfig['unit']" size="small"></el-input-number>&nbsp;件</div>
		<div v-if="dpConfig['price_count_type'] == 2">重量：<el-input-number  :precision="0" :min=0 v-model="dpConfig['unit']" size="small"></el-input-number>&nbsp;g</div>
		<div>成本：￥{{(rowCbPrice(dpConfig)).toFixed(2)}}</div>
				</div>
				</template>
				<el-button type="primary" size="small" @click="dpConfig(scope.row)">配置</el-button>
        		</template>
            </el-table-column>
			<el-table-column width="200px" label="成本价">
	    		<template slot-scope="scope">
					￥{{cbPrice(scope.row).toFixed(2)}}
	    		</template>
	        </el-table-column>
    		<el-table-column width="200px" label="销售价">
        		<template slot-scope="scope">
            		<div v-show="!attrConfig.auto_create_sale_price">
            		￥ <el-input-number v-model="scope.row['sale_price']" :precision="2" :step="0.1" :min="0" size="small"></el-input-number>
					</div>
            		<div v-show="attrConfig.auto_create_sale_price">
            		￥ {{(cbPrice(scope.row)*attrConfig['sale_scale']).toFixed(2)}}
					</div>
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
	    </div>
		<goods-dp-config @confirm-select="confirmSelect" :show-dp-config-dialog.sync="showDpConfigDialog"></goods-dp-config></div>
	`,
	data:function(){
		return {
			skuTableData:[],
			showDpConfigDialog:false,
			dpConfigRow:null
		};
	},
	methods:{
		skuExtRowProp:function(row){
			return {
				stock:10000000,
				dpc:[],
				sale_price:0
			};
		},
		getFormData:function(){
			var form = [];
			console.log(this.skuTableData);
			for(var i in this.skuTableData)
			{
				var cpConfig = [];
				for(var j in this.skuTableData[i]['dpc'])
				{
					var attr = [];
					if(this.skuTableData[i]['dpc'][j]['selectAttrs'])
    					for(var f in this.skuTableData[i]['dpc'][j]['selectAttrs'])
    					{
    						attr.push({
    							attr_id:this.skuTableData[i]['dpc'][j]['selectAttrs'][f]['attr']['id'],
    							item_id:this.skuTableData[i]['dpc'][j]['selectAttrs'][f]['item']['id']
            				});
    					}
					cpConfig.push({
						goods_id:this.skuTableData[i]['dpc'][j]['id'],
						attr:attr,
						unit:this.skuTableData[i]['dpc'][j]['unit']
					})
				}
				form.push({
					sku_id:this.skuTableData[i]['sku_id'],
					cost:null,
					sale_price:this.attrConfig['auto_create_sale_price']?null:this.skuTableData[i]['sale_price'],
					stock:this.skuTableData[i]['stock'],
					cpConfig:cpConfig
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
						if(data[i]['sale_price'])
						{
							row['sale_price'] = data[i]['sale_price'];
						}
						row['dpc'] = data[i]['dpc'];
						console.log(row)
					}
				}
			});
		},
		dpConfig:function(row){
			this.dpConfigRow = row
			this.showDpConfigDialog = true;
		},
		confirmSelect:function(row){
			this.showDpConfigDialog = false;
			if(this.dpConfigRow)
			{
				dpc = this.dpConfigRow['dpc']
				if(!dpc)
				{
					dpc = [];
				}
				dpc.push(Object.assign({unit:''},row)); 
				this.$set(this.dpConfigRow,'dpc',dpc)
			}
			console.log(this.dpConfigRow)
		},
		removeDpConfig:function(row,index){
			if(row['dpc'])
			{
				row['dpc'].splice(index,1);
			}
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
        			   <el-select v-model="form.tags" :collapse-tags="true" :clearable=true :multiple="true">
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
			 	 	 <el-form-item label="售价比">
    			  		<el-input-number :disabled="!attrConfig.auto_create_sale_price" v-model="attrConfig.sale_scale" :min="0.01" :precision="2" :step="0.01" size="small"></el-input-number>
    			  	</el-form-item>
			 	 	 <el-form-item label="自定规格">
			 	 	 	<goods-attr-assign :assign-data.sync="attrAssignData" ref="goodsAttrAssign"
			 	 	 		:attr-options-url="attrOptionsUrl" 
			 	 	 		:attr-fast-create-url="attrFastCreateUrl"
			 	 	 		:item-options-url="itemOptionsUrl"
			 	 	 		:item-fast-create-url="itemFastCreateUrl"></goods-attr-assign>
        			 </el-form-item>
        			 <el-form-item>
			 	 	 	<goods-sku-detail :attr-config="attrConfig" :sku-table-data.sync="detailSkuTableData" v-show="detailSkuTableData.length>0" ref="goodsSkuDetail" :attr-assign-data="attrAssignData"></goods-sku-detail>
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
			attrAssignData:[],
			detailSkuTableData:[],
			typeName:'<?=Goods::$typeMap[$type] ?>',
			categoryNodes:[],
			tagOptions:[],
			stateOptions:<?=HtmlHelper::encodeMapOptions(Goods::$stateMap) ?>,
			attrConfig:{
				auto_create_sale_price:<?=GoodsAttrConfig::AUTO_CREATE_SALE_PRICE_FALSE ?>,
				sale_scale:1
			},
			detailSkuTableData:[]
		}
		return data;
	},
	computed:{
	},
	mounted:function(){
		var _this = this;
		this.$refs['form'].requestInitData(this.initDataUrl,{
			success:function(response){
				_this.categoryNodes = response.data.categoryNodes;
				_this.tagOptions = response.data.tagOptions;
				_this.setAttrConfigFormData(response.data.attrConfigForm);
				_this.$refs['goodsAttrAssign'].setFormData(response.data.attrAssignForm);
				_this.$refs['goodsSkuDetail'].setFormData(response.data.skuDetailForm);
			}
		});
	},
	methods:{
		onSubmit:function(){
			var _this = this;
			if(this.form['images'].length<=0)
			{
				this.$refs['form'].setErrorMessage('至少上传一张商品图片');
				return;
			}
			var attrCount = 0;
			var goodsAttrAssign = this.$refs['goodsAttrAssign'].getFormData()
			attrCount += goodsAttrAssign.length;
			if(attrCount>3)
			{
				this.$refs['form'].setErrorMessage('规格属性最多只能设置3个');
				return;
			}
			this.$refs['form'].submit(this.saveUrl,{
				before:function(data){
					data['attr_config'] = _this.attrConfig;
					data['attr_assign'] = _this.$refs['goodsAttrAssign'].getFormData();
					data['sku_detail'] = _this.$refs['goodsSkuDetail'].getFormData();
					_this.saveLoading = true;
				},
				complete:function(){
					_this.saveLoading = false;
				}
			});
		},
		setAttrConfigFormData:function(attrConfigForm){
			if(attrConfigForm)
			{
				this.attrConfig['auto_create_sale_price'] = attrConfigForm['auto_create_sale_price'];
				this.attrConfig['sale_scale'] = attrConfigForm['sale_scale'];
			}
		},
		onShowUploadImage:function(){
			var categorys = this.$refs['category'].getCheckedNodes();
			var nodeId = ''
			if(categorys.length>0 && categorys[0] && categorys[0]['data']['upload_dir_id'])
			{
				nodeId = categorys[0]['data']['upload_dir_id'];
			}
			this.uploadDirId = nodeId;
		}
	}
});
</script>