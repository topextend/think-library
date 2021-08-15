<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:50:27
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : ValidateHelper.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\helper;

use think\admin\Helper;
use think\Validate;

/**
 * 快捷输入验证器
 * Class ValidateHelper
 * @package think\admin\helper
 */
class ValidateHelper extends Helper
{
    /**
     * 快捷输入并验证（ 支持 规则 # 别名 ）
     * @param array $rules 验证规则（ 验证信息数组 ）
     * @param string|array $input 输入内容 ( post. 或 get. )
     * @param callable|null $callable 异常处理操作
     * @return array
     *  age.require => message // 最大值限定
     *  age.between:1,120 => message // 范围限定
     *  name.require => message // 必填内容
     *  name.default => 100 // 获取并设置默认值
     *  region.value => value // 固定字段数值内容
     *  更多规则参照 ThinkPHP 官方的验证类
     */
    public function init(array $rules, $input = '', ?callable $callable = null): array
    {
        if (is_string($input)) {
            $type = trim($input, '.') ?: 'request';
            $input = $this->app->request->$type();
        }
        [$data, $rule, $info] = [[], [], []];
        foreach ($rules as $name => $message) if (is_numeric($name)) {
            [$name, $alias] = explode('#', $message . '#');
            $data[$name] = $input[($alias ?: $name)] ?? null;
        } elseif (strpos($name, '.') === false) {
            $data[$name] = $message;
        } elseif (preg_match('|^(.*?)\.(.*?)#(.*?)#?$|', $name . '#', $matches)) {
            [, $_key, $_rule, $alias] = $matches;
            if (in_array($_rule, ['value', 'default'])) {
                if ($_rule === 'value') $data[$_key] = $message;
                elseif ($_rule === 'default') $data[$_key] = $input[($alias ?: $_key)] ?? $message;
            } else {
                $info[explode(':', $name)[0]] = $message;
                $data[$_key] = $data[$_key] ?? ($input[($alias ?: $_key)] ?? null);
                $rule[$_key] = isset($rule[$_key]) ? ($rule[$_key] . '|' . $_rule) : $_rule;
            }
        }
        $validate = new Validate();
        if ($validate->rule($rule)->message($info)->check($data)) {
            return $data;
        } elseif (is_callable($callable)) {
            return call_user_func($callable, $validate->getError());
        } else {
            $this->class->error($validate->getError());
        }
    }
}