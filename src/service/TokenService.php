<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:52:24
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : TokenService.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\service;

use think\admin\Service;

/**
 * 表单令牌管理服务
 * Class TokenService
 * @package think\admin\service
 */
class TokenService extends Service
{
    /**
     * 缓存分组名称
     * @var string
     */
    private $name;

    /**
     * 当前缓存数据
     * @var array
     */
    private $cache = [];
    /**
     * 令牌有效时间
     * @var integer
     */
    private $expire = 600;

    /**
     * 令牌服务初始化
     */
    protected function initialize()
    {
        $this->name = $this->getCacheName();
        $this->cache = $this->_getCacheList(true);
        $this->app->event->listen('HttpEnd', function () {
            TokenService::instance()->saveCacheData();
        });
    }

    /**
     * 获取缓存名称
     * @return string
     */
    public function getCacheName(): string
    {
        $sid = $this->app->session->getId();
        return 'systoken_' . ($sid ?: 'default');
    }

    /**
     * 保存缓存到文件
     */
    public function saveCacheData()
    {
        $this->_clearTimeoutCache();
        $this->app->cache->set($this->name, $this->cache, $this->expire);
    }

    /**
     * 获取当前请求 CSRF 值
     * @return array|string
     */
    public function getInputToken(): string
    {
        return $this->app->request->header('user-form-token') ?: input('_csrf_', '');
    }

    /**
     * 验证 CSRF 是否有效
     * @param null|string $token 表单令牌
     * @param null|string $node 授权节点
     * @return boolean
     */
    public function checkFormToken(?string $token = null, ?string $node = null): bool
    {
        $cache = $this->_getCacheItem($token ?: $this->getInputToken());
        if (empty($cache['node']) || empty($cache['time'])) return false;
        return $cache['node'] === NodeService::instance()->fullnode($node);
    }

    /**
     * 清理表单 CSRF 数据
     * @param null|string $token
     * @return $this
     */
    public function clearFormToken(?string $token = null): TokenService
    {
        $this->_delCacheItem($token ?: $this->getInputToken());
        return $this;
    }

    /**
     * 生成表单 CSRF 数据
     * @param null|string $node
     * @return array
     */
    public function buildFormToken(?string $node = null): array
    {
        $cnode = NodeService::instance()->fullnode($node);
        [$token, $time] = [uniqid() . rand(100000, 999999), time()];
        $this->_setCacheItem($token, $item = ['node' => $cnode, 'time' => $time]);
        return array_merge($item, ['token' => $token]);
    }

    /**
     * 清空所有 CSRF 数据
     */
    public function clearCache()
    {
        $this->app->cache->delete($this->name);
    }

    /**
     * 设置缓存数据
     * @param string $token
     * @param array $value
     */
    private function _setCacheItem(string $token, array $value)
    {
        $this->cache[$token] = $value;
    }

    /**
     * 删除缓存
     * @param string $token
     */
    private function _delCacheItem(string $token)
    {
        unset($this->cache[$token]);
    }

    /**
     * 获取指定缓存
     * @param string $token
     * @return mixed
     */
    private function _getCacheItem(string $token)
    {
        $this->_clearTimeoutCache();
        return $this->cache[$token] ?? [];
    }

    /**
     * 获取缓存列表
     * @param bool $clear 强制清理
     * @return array
     */
    private function _getCacheList(bool $clear = false): array
    {
        $this->cache = $this->app->cache->get($this->name, []);
        if ($clear) $this->cache = $this->_clearTimeoutCache();
        return $this->cache;
    }

    /**
     * 清理超时的缓存
     * @return array
     */
    private function _clearTimeoutCache(): array
    {
        $time = time();
        foreach ($this->cache as $key => $item) {
            if (empty($item['time']) || $item['time'] + $this->expire < $time) {
                unset($this->cache[$key]);
            }
        }
        if (count($this->cache) > 999) {
            $this->cache = array_slice($this->cache, -999);
        }
        return $this->cache;
    }
}