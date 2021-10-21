<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:50:19
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : SystemBase.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\model;

use think\admin\Model;

/**
 * 数据字典模型
 * Class SystemBase
 * @package think\admin\model
 */
class SystemBase extends Model
{
    /**
     * 日志名称
     * @var string
     */
    protected $oplogName = '数据字典';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = '数据字典管理';

    /**
     * 获取指定数据列表
     * @param string $type 数据类型
     * @param array $data 外围数据
     * @param string $field 外链字段
     * @param string $bind 绑定字段
     * @return array
     */
    public function items(string $type, array &$data = [], string $field = 'base_code', string $bind = 'base_info'): array
    {
        $map = ['type' => $type, 'status' => 1, 'deleted' => 0];
        $bases = $this->where($map)->order('sort desc,id asc')->column('code,name,content', 'code');
        if (count($data) > 0) foreach ($data as &$vo) $vo[$bind] = $bases[$vo[$field]] ?? [];
        return $bases;
    }

    /**
     * 获取所有数据类型
     * @param boolean $simple
     * @return array
     */
    public function types(bool $simple = false): array
    {
        $types = $this->where(['deleted' => 0])->distinct(true)->column('type');
        if (empty($types) && empty($simple)) $types = ['身份权限'];
        return $types;
    }

    /**
     * 格式化创建时间
     * @param string $value
     * @return string
     */
    public function getCreateAtAttr(string $value): string
    {
        return format_datetime($value);
    }
}