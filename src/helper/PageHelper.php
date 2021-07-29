<?php
// -----------------------------------------------------------------------
// |Author       : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Date         : 2020-07-08 16:36:17
// |----------------------------------------------------------------------
// |LastEditTime : 2020-12-23 21:28:58
// |----------------------------------------------------------------------
// |LastEditors  : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Description  : Class PageHelper
// |----------------------------------------------------------------------
// |FilePath     : \think-library\src\helper\PageHelper.php
// |----------------------------------------------------------------------
// |Copyright (c) 2020 http://www.ladmin.cn   All rights reserved. 
// -----------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\helper;

use think\admin\Helper;
use think\db\BaseQuery;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpResponseException;
use think\Model;

/**
 * 列表处理管理器
 * Class PageHelper
 * @package think\admin\helper
 */
class PageHelper extends Helper
{

    /**
     * 逻辑器初始化
     * @param Model|BaseQuery|string $dbQuery
     * @param boolean $page 是否启用分页
     * @param boolean $display 是否渲染模板
     * @param boolean|integer $total 集合分页记录数
     * @param integer $limit 集合每页记录数
     * @param string $template 模板文件名称
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function init($dbQuery, bool $page = true, bool $display = true, $total = false, int $limit = 0, string $template = ''): array
    {
        $this->query = $this->buildQuery($dbQuery);
        if ($this->app->request->isPost()) $this->_listSort();
        if ($page) {
            if ($limit <= 1) {
                $limit = $this->app->request->get('limit', $this->app->cookie->get('limit', 20));
                if (intval($this->app->request->get('not_cache_limit', 0)) < 1) {
                    $this->app->cookie->set('limit', ($limit = intval($limit >= 5 ? $limit : 20)) . '');
                }
            }
            $get = $this->app->request->get();
            $data = ($paginate = $this->query->paginate(['list_rows' => $limit, 'query' => $get], $total))->toArray();
            $result = ['page' => ['limit' => $data['per_page'], 'total' => $data['total'], 'pages' => $data['last_page'], 'current' => $data['current_page']], 'list' => $data['data']];
            // 分页跳转参数生成
            $select = "<select onchange='location.href=this.options[this.selectedIndex].value'>";
            foreach ([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150, 160, 170, 180, 190, 200] as $num) {
                $url = $this->app->request->baseUrl() . '?' . http_build_query(array_merge($get, ['limit' => $num, 'page' => 1]));
                if (stripos($this->app->request->get('spm', '-'), 'm-') === 0) $url = sysuri('admin/index/index') . '#' . $url;
                $select .= sprintf('<option data-num="%d" value="%s" %s>%d</option>', $num, $url, $limit === $num ? 'selected' : '', $num);
            }
            $link = str_replace('<a href=', '<a data-open=', $paginate->render() ?: '');
            $html = lang('think_library_page_html', [$data['total'], "{$select}</select>", $data['last_page'], $data['current_page']]);
            $this->class->assign('pagehtml', "<div class='pagination-container nowrap'><span>{$html}</span>{$link}</div>");
        } else {
            $result = ['list' => $this->query->select()->toArray()];
        }
        if (false !== $this->class->callback('_page_filter', $result['list']) && $display) {
            if ($this->app->request->get('output') === 'json') {
                $this->class->success('JSON-DATA', $result);
            } else {
                $this->class->fetch($template, $result);
            }
        }
        return $result;
    }

    /**
     * 组件 Layui.Table 处理
     * @param Model|BaseQuery|string $dbQuery
     * @param string $template
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function layTable($dbQuery, string $template = ''): array
    {
        $get = $this->app->request->get();
        $this->query = $this->buildQuery($dbQuery);
        if ($this->app->request->isPost()) $this->_listSort();
        if (($get['output'] ?? '') === 'json') {
            return PageHelper::instance()->init($dbQuery);
        } elseif (($get['output'] ?? '') === 'layui.table') {
            // 根据参数排序
            if (isset($get['_field_']) && isset($get['_order_'])) {
                $this->query->order("{$get['_field_']} {$get['_order_']}");
            }
            // 数据分页处理
            if (isset($get['page']) && isset($get['limit'])) {
                $rows = $get['limit'] ?: 20;
                $data = $this->query->paginate(['list_rows' => $rows, 'query' => $get], false)->toArray();
                $result = ['msg' => '', 'code' => 0, 'count' => $data['total'], 'data' => $data['data']];
            } else {
                $data = $this->query->select()->toArray();
                $result = ['msg' => '', 'code' => 0, 'count' => count($data), 'data' => $data];
            }
            if (false !== $this->class->callback('_page_filter', $result['data'])) {
                throw new HttpResponseException(json($result));
            } else {
                return $result;
            }
        } else {
            $this->class->fetch($template);
        }
    }

    /**
     * 数据列表排序自动处理
     * @throws DbException
     */
    private function _listSort()
    {
        if ($this->app->request->isPost() && $this->app->request->post('action') === 'sort') {
            if (method_exists($this->query, 'getTableFields') && in_array('sort', $this->query->getTableFields())) {
                if ($this->app->request->has($pk = $this->query->getPk() ?: 'id', 'post')) {
                    $map = [$pk => $this->app->request->post($pk, 0)];
                    $data = ['sort' => intval($this->app->request->post('sort', 0))];
                    if ($this->app->db->table($this->query->getTable())->where($map)->update($data) !== false) {
                        $this->class->success(lang('think_library_sort_success'), '');
                    }
                }
            }
            $this->class->error(lang('think_library_sort_error'));
        }
    }
}
