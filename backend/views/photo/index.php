<?php 
use common\librarys\Url;

?>
<!DOCTYPE html>
<html>
<head>
<title>素材中心 - 打荷鲜生后台</title>
<meta http-equiv="Content-Type"
	content="text/html; charset=UTF-8" />
<meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/>
<script src="<?=APP_URL_ASSET ?>/backend/vendor/all.min.js"></script>
<link rel="stylesheet" href="<?=APP_URL_ASSET ?>/backend/vendor/all.min.css">
</head>
<style>
body{
	margin: 0px;
}
.image{
	border: 1px solid #f2f2f2;
    width: 7.2rem;
    height: 7.2rem;
}
</style>
<script>
$util = {
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
		}
	};
</script>
<body>
<div id="page" v-cloak>
<div v-infinite-scroll="load" :infinite-scroll-disabled="!hasNext" style="overflow:auto" :infinite-scroll-immediate="true">
<el-image v-for="item in items" class="image" :src="item" fit="fit" :lazy="true" @click="zoom(item)"></el-image>
</div> 
</body>
</html>
<script>
var vue = new Vue({
	el:'#page',
	data:function(){
		return {
			page:1,
			items:[],
			hasNext:true,
			loading:false
		};
	},
	methods:{
		zoom:function(url){
			WeixinJSBridge.invoke('imagePreview', {
			    'urls': this.items,
			    'current': url
			  });
		},
		load:function(){
			if(this.loading)
			{
				return;
			}
			var _this = this
			this.loading = true;
			$util.request('<?=Url::toRoute('list-data') ?>',{
				params:{
					dir_id:'<?=$_GET['dir_id'] ?>',
					page:_this.page
				},
				success:function(responce){
					_this.hasNext = responce.data.hasNext;
					for(var i in responce.data.rows)
					{
						_this.items.push(responce.data.rows[i]);
					}
					_this.page ++;
				},
				complete:function(){
					_this.loading = false;
				}
			});
		}
	}
});
</script>