<?php
// -----------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-01 17:12:07
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : TokenHelper.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// -----------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\helper;

use think\admin\Helper;
use think\admin\service\TokenService;
use think\exception\HttpResponseException;

/**
 * 表单令牌验证器
 * Class TokenHelper
 * @package think\admin\helper
 */
class TokenHelper extends Helper
{

    /**
     * 初始化验证码器
     * @param boolean $return
     * @return boolean
     */
    public function init(bool $return = false): bool
    {
        $this->class->csrf_state = true;
        if ($this->app->request->isPost() && !TokenService::instance()->checkFormToken()) {
            if ($return) return false;
            $this->class->error($this->class->csrf_message ?: lang('think_library_csrf_error'));
        } else {
            return true;
        }
    }

    /**
     * 清理表单令牌
     */
    public function clear()
    {
        TokenService::instance()->clearFormToken();
    }

    /**
     * 返回视图内容
     * @param string $tpl 模板名称
     * @param array $vars 模板变量
     * @param string|null $node 授权节点
     */
    public function fetchTemplate(string $tpl = '', array $vars = [], ?string $node = null)
    {
        throw new HttpResponseException(view($tpl, $vars, 200, function ($html) use ($node) {
            return preg_replace_callback('/<\/form>/i', function () use ($node) {
                $csrf = TokenService::instance()->buildFormToken($node);
                return "<input type='hidden' name='_token_' value='{$csrf['token']}'></form>";
            }, $html);
        }));
    }

}