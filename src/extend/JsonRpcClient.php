<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:49:39
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : JsonRpcClient.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\extend;

use think\admin\Exception;

/**
 * JsonRpc 客户端
 * Class JsonRpcClient
 * @package think\admin\extend
 */
class JsonRpcClient
{
    /**
     * 请求ID
     * @var integer
     */
    private $id;

    /**
     * 服务端地址
     * @var string
     */
    private $proxy;

    /**
     * JsonRpcClient constructor.
     * @param string $proxy
     */
    public function __construct(string $proxy)
    {
        $this->proxy = $proxy;
        $this->id = CodeExtend::uniqidNumber();
    }

    /**
     * 执行 JsonRpc 请求
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function __call(string $method, array $params = [])
    {
        $options = [
            'ssl'  => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => json_encode([
                    'jsonrpc' => '2.0', 'method' => $method, 'params' => $params, 'id' => $this->id,
                ], JSON_UNESCAPED_UNICODE),
            ],
        ];
        // Performs the HTTP POST
        if ($fp = fopen($this->proxy, 'r', false, stream_context_create($options))) {
            $response = '';
            while ($row = fgets($fp)) $response .= trim($row) . "\n";
            [, $response] = [fclose($fp), json_decode($response, true)];
        } else {
            throw new Exception("无法连接到 {$this->proxy}");
        }
        // Final checks and return
        if ($response['id'] != $this->id) {
            throw new Exception("错误标记 (请求标记: {$this->id}, 响应标记: {$response['id']}）");
        }
        if (is_null($response['error'])) {
            return $response['result'];
        } else {
            throw new Exception($response['error']['message'], $response['error']['code'], $response['result']);
        }
    }
}