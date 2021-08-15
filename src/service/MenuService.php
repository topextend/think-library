<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-07-29 17:30:09
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-10 18:41:06
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : MenuService.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\service;

use ReflectionException;
use think\admin\extend\DataExtend;
use think\admin\Service;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 系统菜单管理服务
 * Class MenuService
 * @package app\admin\service
 */
class MenuService extends Service
{

    /**
     * 获取可选菜单节点
     * @return array
     * @throws ReflectionException
     */
    public function getList(): array
    {
        static $nodes = [];
        if (count($nodes) > 0) return $nodes;
        foreach (NodeService::instance()->getMethods() as $node => $method) {
            if ($method['ismenu']) $nodes[] = ['node' => $node, 'title' => $method['title']];
        }
        return $nodes;
    }

    /**
     * 获取系统菜单树数据
     * @return array
     * @throws ReflectionException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getTree(): array
    {
        $query = $this->app->db->name('SystemMenu');
        $query->where(['status' => 1])->order('sort desc,id asc');
        return $this->_buildData(DataExtend::arr2tree($query->select()->toArray()));
    }

    /**
     * 后台主菜单权限过滤
     * @param array $menus 当前菜单列表
     * @return array
     * @throws ReflectionException
     */
    private function _buildData(array $menus): array
    {
        $service = AdminService::instance();
        foreach ($menus as $key => &$menu) {
            if (!empty($menu['sub'])) {
                $menu['sub'] = $this->_buildData($menu['sub']);
            }
            if (!empty($menu['sub'])) {
                $menu['url'] = '#';
            } elseif ($menu['url'] === '#') {
                unset($menus[$key]);
            } elseif (preg_match('|^https?://|i', $menu['url'])) {
                if (!!$menu['node'] && !$service->check($menu['node'])) {
                    unset($menus[$key]);
                } elseif ($menu['params']) {
                    $menu['url'] .= (strpos($menu['url'], '?') === false ? '?' : '&') . $menu['params'];
                }
            } elseif (!!$menu['node'] && !$service->check($menu['node'])) {
                unset($menus[$key]);
            } else {
                $node = join('/', array_slice(explode('/', $menu['url']), 0, 3));
                $addons = substr_count($menu['url'], '/') > 2 && preg_match('/addons/', $menu['url']) ? "/addons" : '';
                $menu['url'] = $addons. url($menu['url'])->build() . ($menu['params'] ? '?' . $menu['params'] : '');
                if (!$service->check($node)) unset($menus[$key]);
            }
        }
        return $menus;
    }
}