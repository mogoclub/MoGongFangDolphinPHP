<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

/**
 * 菜单信息
 */
return [
  [
    'title' => '评论',
    'icon' => 'fa fa-fw fa-comment',
    'url_type' => 'module_admin',
    'url_value' => 'comments/index/index',
    'url_target' => '_self',
    'online_hide' => 0,
    'sort' => 100,
    'status' => 1,
    'child' => [
      [
        'title' => '评论管理',
        'icon' => 'fa fa-fw fa-comments-o',
        'url_type' => 'module_admin',
        'url_value' => '',
        'url_target' => '_self',
        'online_hide' => 0,
        'sort' => 100,
        'status' => 1,
        'child' => [
          [
            'title' => '评论列表',
            'icon' => 'fa fa-fw fa-th-large',
            'url_type' => 'module_admin',
            'url_value' => 'comments/index/index',
            'url_target' => '_self',
            'online_hide' => 0,
            'sort' => 100,
            'status' => 1,
            'child' => [
              [
                'title' => '新增',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'comments/index/add',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '编辑',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'comments/index/edit',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '删除',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'comments/index/delete',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '启用',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'comments/index/enable',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '禁用',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'comments/index/disable',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
              [
                'title' => '快速编辑',
                'icon' => '',
                'url_type' => 'module_admin',
                'url_value' => 'comments/index/quickedit',
                'url_target' => '_self',
                'online_hide' => 0,
                'sort' => 100,
                'status' => 1,
              ],
            ],
          ],
        ],
      ],
    ],
  ],
];
