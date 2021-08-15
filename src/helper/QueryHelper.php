<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:50:10
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : QueryHelper.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\helper;

use think\admin\Helper;
use think\db\BaseQuery;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\Model;

/**
 * 搜索条件处理器
 * Class QueryHelper
 * @package think\admin\helper
 * @see \think\db\Query
 * @mixin Query
 */
class QueryHelper extends Helper
{
    /**
     * 分页助手工具
     * @var PageHelper
     */
    protected $page;

    /**
     * 当前数据操作
     * @var Query
     */
    protected $query;

    /**
     * 初始化默认数据
     * @var array
     */
    protected $input;

    /**
     * 获取当前Db操作对象
     * @return Query
     */
    public function db(): Query
    {
        return $this->query;
    }

    /**
     * 逻辑器初始化
     * @param Model|BaseQuery|string $dbQuery
     * @param string|array|null $input 输入数据
     * @return $this
     * @throws DbException
     */
    public function init($dbQuery, $input = null): QueryHelper
    {
        $this->page = PageHelper::instance();
        $this->input = $this->getInputData($input);
        $this->query = $this->page->autoSortQuery($dbQuery);
        return $this;
    }

    /**
     * 设置 Like 查询条件
     * @param string|array $fields 查询字段
     * @param string $split 前后分割符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function like($fields, string $split = '', $input = null, string $alias = '#'): QueryHelper
    {
        $data = $this->getInputData($input ?: $this->input);
        foreach (is_array($fields) ? $fields : explode(',', $fields) as $field) {
            [$dk, $qk] = [$field, $field];
            if (stripos($field, $alias) !== false) {
                [$dk, $qk] = explode($alias, $field);
            }
            if (isset($data[$qk]) && $data[$qk] !== '') {
                $this->query->whereLike($dk, "%{$split}{$data[$qk]}{$split}%");
            }
        }
        return $this;
    }

    /**
     * 设置 Equal 查询条件
     * @param string|array $fields 查询字段
     * @param string|array|null $input 输入类型
     * @param string $alias 别名分割符
     * @return $this
     */
    public function equal($fields, $input = null, string $alias = '#'): QueryHelper
    {
        $data = $this->getInputData($input ?: $this->input);
        foreach (is_array($fields) ? $fields : explode(',', $fields) as $field) {
            [$dk, $qk] = [$field, $field];
            if (stripos($field, $alias) !== false) {
                [$dk, $qk] = explode($alias, $field);
            }
            if (isset($data[$qk]) && $data[$qk] !== '') {
                $this->query->where($dk, "{$data[$qk]}");
            }
        }
        return $this;
    }

    /**
     * 设置 IN 区间查询
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function in($fields, string $split = ',', $input = null, string $alias = '#'): QueryHelper
    {
        $data = $this->getInputData($input ?: $this->input);
        foreach (is_array($fields) ? $fields : explode(',', $fields) as $field) {
            [$dk, $qk] = [$field, $field];
            if (stripos($field, $alias) !== false) {
                [$dk, $qk] = explode($alias, $field);
            }
            if (isset($data[$qk]) && $data[$qk] !== '') {
                $this->query->whereIn($dk, explode($split, $data[$qk]));
            }
        }
        return $this;
    }

    /**
     * 设置内容区间查询
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function valueBetween($fields, string $split = ' ', $input = null, string $alias = '#'): QueryHelper
    {
        return $this->_setBetweenWhere($fields, $split, $input, $alias);
    }

    /**
     * 设置日期时间区间查询
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function dateBetween($fields, string $split = ' - ', $input = null, string $alias = '#'): QueryHelper
    {
        return $this->_setBetweenWhere($fields, $split, $input, $alias, function ($value, $type) {
            if (preg_match('#^\d{4}(-\d\d){2}\s+\d\d(:\d\d){2}$#', $value)) return $value;
            else return $type === 'after' ? "{$value} 23:59:59" : "{$value} 00:00:00";
        });
    }

    /**
     * 设置时间戳区间查询
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @return $this
     */
    public function timeBetween($fields, string $split = ' - ', $input = null, string $alias = '#'): QueryHelper
    {
        return $this->_setBetweenWhere($fields, $split, $input, $alias, function ($value, $type) {
            if (preg_match('#^\d{4}(-\d\d){2}\s+\d\d(:\d\d){2}$#', $value)) return strtotime($value);
            else return $type === 'after' ? strtotime("{$value} 23:59:59") : strtotime("{$value} 00:00:00");
        });
    }

    /**
     * 实例化分页管理器
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
    public function page(bool $page = true, bool $display = true, $total = false, int $limit = 0, string $template = ''): array
    {
        return $this->page->init($this->query, $page, $display, $total, $limit, $template);
    }

    /**
     * 清空数据并保留表结构
     * @return $this
     */
    public function empty(): QueryHelper
    {
        $table = $this->query->getTable();
        $this->app->db->execute("truncate table `{$table}`");
        return $this;
    }

    /**
     * Layui.Table 组件数据
     * @param ?callable $befor 表单前置操作
     * @param ?callable $after 表单后置操作
     * @param string $template 前端模板文件
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function layTable(?callable $befor = null, ?callable $after = null, string $template = '')
    {
        if ($this->output === 'get.layui.table') {
            if (is_callable($after)) {
                call_user_func($after, $this, $this->query);
            }
            $this->page->layTable($this->query, $template);
        } else {
            if (is_callable($befor)) {
                call_user_func($befor, $this, $this->query);
            }
            $this->class->fetch($template);
        }
    }

    /**
     * QueryHelper call.
     * @param string $name 调用方法名称
     * @param array $args 调用参数内容
     * @return $this
     */
    public function __call(string $name, array $args): QueryHelper
    {
        if (is_callable($callable = [$this->query, $name])) {
            call_user_func_array($callable, $args);
        }
        return $this;
    }

    /**
     * 设置区域查询条件
     * @param string|array $fields 查询字段
     * @param string $split 输入分隔符
     * @param string|array|null $input 输入数据
     * @param string $alias 别名分割符
     * @param callable|null $callback 回调函数
     * @return $this
     */
    private function _setBetweenWhere($fields, string $split = ' ', $input = null, string $alias = '#', ?callable $callback = null): QueryHelper
    {
        $data = $this->getInputData($input ?: $this->input);
        foreach (is_array($fields) ? $fields : explode(',', $fields) as $field) {
            [$dk, $qk] = [$field, $field];
            if (stripos($field, $alias) !== false) {
                [$dk, $qk] = explode($alias, $field);
            }
            if (isset($data[$qk]) && $data[$qk] !== '') {
                [$begin, $after] = explode($split, $data[$qk]);
                if (is_callable($callback)) {
                    $after = call_user_func($callback, $after, 'after');
                    $begin = call_user_func($callback, $begin, 'begin');
                }
                $this->query->whereBetween($dk, [$begin, $after]);
            }
        }
        return $this;
    }

    /**
     * 获取输入数据
     * @param string|array|null $input
     * @return array
     */
    private function getInputData($input): array
    {
        if (is_array($input)) {
            return $input;
        } else {
            $input = $input ?: 'request';
            return $this->app->request->$input();
        }
    }
}
