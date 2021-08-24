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
use think\db\Mongo;
use think\db\Query;
use think\helper\Str;
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
     * 实例对象反射
     * @param array $args
     * @return static
     */
    public static function instance(...$args): Helper
    {
        return Container::getInstance()->invokeClass(static::class, $args);
    }

    /**
     * 获取数据库查询对象
     * @param Model|BaseQuery|string $query
     * @return Query|Mongo|BaseQuery
     */
    public static function buildQuery($query)
    {
        if (is_string($query)) {
            return self::buildModel($query)->db();
        }
        if ($query instanceof Model) return $query->db();
        if ($query instanceof BaseQuery && !$query->getModel()) {
            $query->model(self::buildModel($query->getName()));
        }
        return $query;
    }

    /**
     * 动态创建模型对象
     * @param mixed $name 模型名称
     * @param array $data 初始数据
     * @param mixed $conn 指定连接
     * @return Model
     */
    public static function buildModel(string $name, array $data = [], string $conn = ''): Model
    {
        if (strpos($name, '\\') !== false && class_exists($name)) {
            $model = new $name($data);
            if ($model instanceof Model) return $model;
            $name = basename(str_replace('\\', '/', $name));
        }
        $model = new class extends \think\Model {
            public static $NAME = null;
            public static $CONN = null;

            public function __construct(array $data = [])
            {
                if (is_string(self::$NAME)) {
                    $this->name = self::$NAME;
                    $this->connection = self::$CONN;
                    parent::__construct($data);
                }
            }
        };
        $model::$CONN = $conn;
        $model::$NAME = Str::studly($name);
        return $model->newInstance($data);
    }
}