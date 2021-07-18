<?php
// -----------------------------------------------------------------------
// |Author       : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Date         : 2020-07-08 16:36:17
// |----------------------------------------------------------------------
// |LastEditTime : 2020-12-23 21:32:41
// |----------------------------------------------------------------------
// |LastEditors  : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Description  : Class Helper
// |----------------------------------------------------------------------
// |FilePath     : \think-library\src\Helper.php
// |----------------------------------------------------------------------
// |Copyright (c) 2020 http://www.ladmin.cn   All rights reserved. 
// -----------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin;

use think\App;
use think\Container;
use think\db\BaseQuery;
use think\db\Query;
use think\Model;

/**
 * 控制器挂件
 * Class Helper
 * @package think\admin
 */
abstract class Helper
{
    /**
     * 应用容器
     * @var App
     */
    public $app;

    /**
     * 数据模型实例
     * @var Model
     */
    public $model;

    /**
     * 数据查询实例
     * @var Query
     */
    public $query;

    /**
     * 控制器实例
     * @var Controller
     */
    public $class;

    /**
     * Helper constructor.
     * @param App $app
     * @param Controller $class
     */
    public function __construct(Controller $class, App $app)
    {
        $this->app = $app;
        $this->class = $class;
    }

    /**
     * 获取数据库对象
     * @param Model|BaseQuery|string $dbQuery
     * @return Query|mixed
     */
    protected function buildQuery($dbQuery)
    {
        if (is_string($dbQuery)) {
            $this->query = $this->app->db->name($dbQuery);
        } elseif ($dbQuery instanceof Model) {
            $this->model = $dbQuery;
            $this->query = $dbQuery->db();
        } elseif ($dbQuery instanceof BaseQuery) {
            $this->query = $dbQuery;
        }
        return $this->query;
    }

    /**
     * 实例对象反射
     * @param array $args
     * @return static
     */
    public static function instance(...$args): Helper
    {
        return Container::getInstance()->invokeClass(static::class, $args);
    }
}