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
// |@FilePath     : SystemMenu.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\model;

use think\admin\Model;

/**
 * 系统菜单模型
 * Class SystemMenu
 * @package think\admin\model
 */
class SystemMenu extends Model
{
    /**
     * 日志名称
     * @var string
     */
    protected $oplogName = '系统菜单';

    /**
     * 日志类型
     * @var string
     */
    protected $oplogType = '系统菜单管理';

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