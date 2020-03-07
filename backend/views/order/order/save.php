<?php
use common\librarys\Url;
use app\models\SystemUser;
use common\librarys\HtmlHelper;

$this->title = '编辑订单';
$frameConfigs = [
    'paths'=>[
        [
            'name'=>'订单管理',
            'url'=>Url::toRoute('manage')
        ],
        $this->title
    ],
    'activeMenu'=>[3]
];
?>
<body class="frame-container">
	<div id="page" v-cloak>
		<?=$this->beginFrameContent($frameConfigs)?>
			<form-content ref="form" :model="form">
				<div class="form-wrap">
					<div class="form-item-wrap">
						 <el-form-item label="单号">
            			   	<div class="form-item-inner-text">{{infoData.order_num}}</div>
            			 </el-form-item>
						 <el-form-item label="有赞订单号">
            			   	<div class="form-item-inner-text">{{infoData.tid}}</div>
            			 </el-form-item>
            			 <el-form-item label="状态">
            			   	<div class="form-item-inner-text">{{infoData.order_state}}</div>
            			 </el-form-item>
            			 <el-form-item label="总台打印时间">
            			   	<div class="form-item-inner-text">{{infoData.zt_print_state}}<br/>
            			   	<?php 
            			   	 if($model['zt_print_state'] > 0)
            			   	 {
            			   	     ?>
            			   	     <el-checkbox :true-label="1" :false-label="0" v-model="form.reset_zt_print">重新打印</el-checkbox>
            			   	     <?php
            			   	 }
            			   	?>
            			   	</div>
            			 </el-form-item>
            			 <el-form-item label="分控打印时间">
            			   	<div class="form-item-inner-text">{{infoData.fk_print_state}}<br/>
            			   	<?php 
            			   	 if($model['fk_print_state'] > 0)
            			   	 {
            			   	     ?>
            			   	     <el-checkbox :true-label="1" :false-label="0" v-model="form.reset_fk_print">重新打印</el-checkbox>
            			   	     <?php
            			   	 }
            			   	?>
            			   	</div>
            			 </el-form-item>
            			 <el-form-item label="推送时间">
            			   	<div class="form-item-inner-text">{{infoData.created_time}}</div>
            			 </el-form-item>
            			 <el-form-item label="推送更新时间">
            			   	<div class="form-item-inner-text">{{infoData.update_time}}</div>
            			 </el-form-item>
        			 </div>
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
			saveUrl:'<?=Url::toRoute(['','id'=>$model['id']]) ?>',
			saveLoading:false,
			form:{
				reset_zt_print:0,
				reset_fk_print:0,
			},
			infoData:<?=HtmlHelper::encodeJson($infoData) ?>
		}
		return data;
	},
	methods:{
		onSubmit:function(){
			var _this = this;
			this.$refs['form'].submit(this.saveUrl,{
				before:function(){
					_this.saveLoading = true;
				},
				complete:function(){
					_this.saveLoading = false;
				}
			});
		}
	}
});
</script>