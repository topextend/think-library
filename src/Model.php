<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-09 11:08:45
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-09 11:12:44
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : Model.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin;

/**
 * 基础模型类
 * Class Model
 * @package think\admin
 * @see \think\db\Query
 * @mixin \think\db\Query
 */
abstract class Model extends \think\Model
{
    protected $autoWriteTimestamp = false;

    /**
     * 日志名称
     * @var string
     */
    protected $oplogName;

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType;

    /**
     * 修改状态默认处理
     * @param string $ids
     */
    public function onAdminSave(string $ids)
    {
        if ($this->oplogType && $this->oplogName) {
            sysoplog($this->oplogType, "修改{$this->oplogName}[{$ids}]状态");
        }
    }

    /**
     * 更新事件默认处理
     * @param string $ids
     */
    public function onAdminUpdate(string $ids)
    {
        if ($this->oplogType && $this->oplogName) {
            sysoplog($this->oplogType, "更新{$this->oplogName}[{$ids}]成功");
        }
    }

    /**
     * 新增事件默认处理
     * @param string $ids
     */
    public function onAdminInsert(string $ids)
    {
        if ($this->oplogType && $this->oplogName) {
            sysoplog($this->oplogType, "增加{$this->oplogName}[{$ids}]成功");
        }
    }

    /**
     * 删除事件默认处理
     * @param string $ids
     */
    public function onAdminDelete(string $ids)
    {
        if ($this->oplogType && $this->oplogName) {
            sysoplog($this->oplogType, "删除{$this->oplogName}[{$ids}]成功");
        }
    }
}