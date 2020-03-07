<?php
return [
    [
        'name' => '主页',
        'icon' => 'el-icon-menu',
        'route' => 'site/index',
        'allow' => true
    ],
    [
        'name' => '系统',
        'icon' => 'el-icon-setting',
        'subTitle' => '系统管理',
        'sub' => [
            [
                'name' => '用户管理',
                'route' => 'system/user/manage'
            ],
            [
                'name' => '同步日志',
                'route' => 'system/youzan-syn-log/manage'
            ]
        ]
    ],
    [
        'name' => '商品',
        'icon' => 'el-icon-goods',
        'subTitle' => '商品管理',
        'sub' => [
            [
                'name' => '商品分类',
                'route' => 'goods/category/manage'
            ],
            [
                'name' => '商品标签',
                'route' => 'goods/tag/manage'
            ],
            [
                'name' => '商品管理',
                'route' => 'goods/goods/manage'
            ]
        ]
    ],
    [
        'name' => '订单',
        'icon' => 'el-icon-s-order',
        'route' => 'order/order/manage',
        'allow' => true
    ],
    [
        'name' => '上传',
        'icon' => 'el-icon-upload',
        'subTitle' => '上传管理',
        'sub' => [
            [
                'name' => '目录管理',
                'route' => 'upload/dir/manage'
            ],
            [
                'name' => '图片管理',
                'route' => 'upload/image/manage'
            ]
        ]
    ]
];

