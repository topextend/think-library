<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:53:12
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : Helper.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
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
     * 控制器实例
     * @var Controller
     */
    public $class;

    /**
     * 当前请求方式
     * @var string
     */
    public $method;

    /**
     * 自定输出格式
     * @var string
     */
    public $output;

    /**
     * Helper constructor.
     * @param App $app
     * @param Controller $class
     */
    public function __construct(App $app, Controller $class)
    {
        $this->app = $app;
        $this->class = $class;
        // 计算指定输出格式
        $this->method = $app->request->method() ?: ($app->request->isCli() ? 'cli' : 'nil');
        $this->output = strtolower("{$this->method}.{$app->request->request('output', 'default')}");
    }

    /**
     * 获取数据库对象
     * @param Model|BaseQuery|string $dbQuery
     * @return Query|mixed
     */
    protected function buildQuery($dbQuery)
    {
        if (is_string($dbQuery)) {
            $isClass = stripos($dbQuery, '\\') !== false;
            $dbQuery = $isClass ? new $dbQuery : $this->app->db->name($dbQuery);
        }
        if ($dbQuery instanceof Query) return $dbQuery;
        if ($dbQuery instanceof Model) return $dbQuery->db();
        return $dbQuery;
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