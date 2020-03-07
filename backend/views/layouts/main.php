<?php
use yii\helpers\Html;
use common\librarys\Validator;
use common\librarys\UploadFile;
use common\models\BaseConfig;
use common\librarys\Url;

$baseConfig = BaseConfig::getAll([
    BaseConfig::KEY_WEB_NAME,
    BaseConfig::KEY_WEB_LOGO
]);
$webName = $baseConfig[BaseConfig::KEY_WEB_NAME];
$webLogo = $baseConfig[BaseConfig::KEY_WEB_LOGO];
$titleSuffix = "{$webName}后台";
$this->title = Validator::isEmptyString($this->title) ? $titleSuffix : "{$this->title} - {$titleSuffix}";
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Html::encode($this->title) ?></title>
<meta http-equiv="Content-Type"
	content="text/html; charset=<?=ENV_CHARSET ?>" />
<?php if(! Validator::isEmptyString($webLogo)):?>
<link type="image/x-icon" rel="shortcut icon"
	href="<?=UploadFile::getImageUrl($webLogo,'34x34') ?>" />
<?php endif;?>
<?php if($this->includeBaseAsset):?>
<script src="<?=APP_URL_ASSET ?>/backend/vendor/all.min.js"></script>
<script src="<?=Url::toRoute('/common/js-config') ?>"></script>
<script src="<?=APP_URL_ASSET ?>/backend/js/base.js"></script>
<link rel="stylesheet" href="<?=APP_URL_ASSET ?>/backend/vendor/all.min.css">
<link rel="stylesheet" href="<?=APP_URL_ASSET ?>/backend/css/base.css">
<?php endif;?>
</head>
<?=$content?>
</html>