<?php
use common\librarys\Url;

return [
    'imageUpload' => [
        'imageListUrl' => Url::toRoute('/upload/image/up-list-data'),
        'dirOptionsUrl' => Url::toRoute('/upload/dir/up-options-data'),
        'uploadUrl' => Url::toRoute('/upload/image/upload'),
        'uploadRecordUrl' => Url::toRoute('/upload/image/record'),
        'extName' => explode(',', UPLOAD_IMAGE_EXT_NAMES),
        'maxSize' => UPLOAD_IMAGE_MAX_SIZE
    ],
    'table' => [
        'pageSize' => 20
    ]
];

