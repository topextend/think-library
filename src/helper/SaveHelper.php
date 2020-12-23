<?php
// -----------------------------------------------------------------------
// |Author       : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Date         : 2020-07-08 16:36:17
// |----------------------------------------------------------------------
// |LastEditTime : 2020-12-23 21:29:15
// |----------------------------------------------------------------------
// |LastEditors  : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Description  : Class SaveHelper
// |----------------------------------------------------------------------
// |FilePath     : \think-library\src\helper\SaveHelper.php
// |----------------------------------------------------------------------
// |Copyright (c) 2020 http://www.ladmin.cn   All rights reserved. 
// -----------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\helper;

use think\admin\Helper;
use think\db\Query;

/**
 * 数据更新管理器
 * Class SaveHelper
 * @package think\admin\helper
 */
class SaveHelper extends Helper
{

    /**
     * 逻辑器初始化
     * @param Query|string $dbQuery
     * @param array $data 表单扩展数据
     * @param string $field 数据对象主键
     * @param array $where 额外更新条件
     * @return boolean|void
     * @throws \think\db\exception\DbException
     */
    public function init($dbQuery, array $data = [], string $field = '', array $where = [])
    {
        $query = $this->buildQuery($dbQuery);
        $data = $data ?: $this->app->request->post();
        $field = $field ?: ($query->getPk() ?: 'id');
        $value = $this->app->request->post($field, null);
        // 主键限制处理
        if (!isset($where[$field]) && is_string($value)) {
            $query->whereIn($field, explode(',', $value));
            if (isset($data)) unset($data[$field]);
        }
        // 前置回调处理
        if (false === $this->class->callback('_save_filter', $query, $data)) {
            return false;
        }
        // 执行更新操作
        $result = $query->where($where)->update($data) !== false;
        // 结果回调处理
        if (false === $this->class->callback('_save_result', $result)) {
            return $result;
        }
        // 回复前端结果
        if ($result !== false) {
            $this->class->success(lang('think_library_save_success'), '');
        } else {
            $this->class->error(lang('think_library_save_error'));
        }
    }

}
