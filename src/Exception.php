<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:53:08
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : Exception.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
namespace think\admin;

/**
 * 自定义数据异常
 * Class Exception
 * @package think\admin
 */
class Exception extends \Exception
{
    /**
     * 异常数据对象
     * @var mixed
     */
    protected $data = [];

    /**
     * Exception constructor.
     * @param string $message
     * @param integer $code
     * @param mixed $data
     */
    public function __construct($message = "", $code = 0, $data = [])
    {
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
        parent::__construct($message, $code);
    }

    /**
     * 获取异常停止数据
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 设置异常停止数据
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}