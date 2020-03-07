<?php
namespace app\services;

use common\librarys\UploadHelper;

class BaseService extends \common\services\BaseService
{

    public function getImageValue($item, $size = UploadHelper::SIZE_MED)
    {
        return [
            'id'=>$item['id'],
            'url' => $item['url'],
            'thumb_url' => UploadHelper::getImageUrl($item['url'], $size),
            'src_url' => UploadHelper::getImageUrl($item['url'], null)
        ];
    }

    public function getImageListValue($list)
    {
        $result = [];
        if ($list)
            foreach ($list as $item) {
                $result[] = $this->getImageValue($item);
            }
        return $result;
    }
}
