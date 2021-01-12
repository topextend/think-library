<?php
// -----------------------------------------------------------------------
// |Author       : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Date         : 2020-07-08 16:36:17
// |----------------------------------------------------------------------
// |LastEditTime : 2020-08-13 19:43:24
// |----------------------------------------------------------------------
// |LastEditors  : Jarmin <edshop@qq.com>
// |----------------------------------------------------------------------
// |Description  : Class Version
// |----------------------------------------------------------------------
// |FilePath     : \think-library\src\multiple\command\Clear.php
// |----------------------------------------------------------------------
// |Copyright (c) 2020 http://www.ladmin.cn   All rights reserved. 
// -----------------------------------------------------------------------
declare (strict_types=1);
namespace think\admin\multiple\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

/**
 * 清理运行缓存
 * Class Clear
 * @package think\admin\multiple\command
 */
class Clear extends Command
{
    protected function configure()
    {
        $this->setName('clear')->addArgument('app', Argument::OPTIONAL, 'app name');
        $this->addOption('path', 'd', Option::VALUE_OPTIONAL, 'path to clear', null);
        $this->addOption('cache', 'c', Option::VALUE_NONE, 'clear cache file');
        $this->addOption('log', 'l', Option::VALUE_NONE, 'clear log file');
        $this->addOption('dir', 'r', Option::VALUE_NONE, 'clear empty dir');
        $this->addOption('expire', 'e', Option::VALUE_NONE, 'clear cache file if cache has expired');
        $this->setDescription('Clear runtime file');
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     */
    protected function execute(Input $input, Output $output)
    {
        $app = $input->getArgument('app') ?: '';
        $runtimePath = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR . ($app ? $app . DIRECTORY_SEPARATOR : '');
        if ($input->getOption('cache')) {
            $path = $runtimePath . 'cache';
        } elseif ($input->getOption('log')) {
            $path = $runtimePath . 'log';
        } else {
            $path = $input->getOption('path') ?: $runtimePath;
        }
        $rmdir = $input->getOption('dir') ? true : false;
        // --expire 仅当 --cache 时生效
        $expire = $input->getOption('expire') && $input->getOption('cache');
        $this->clear(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $rmdir, $expire);
        $output->writeln("<info>Clear Successed</info>");
    }

    /**
     * 清处理指定目录
     * @param string $path 待清理目录
     * @param boolean $rmdir 是否目录
     * @param boolean $expire 有效时间
     */
    private function clear(string $path, bool $rmdir, bool $expire): void
    {
        foreach (is_dir($path) ? scandir($path) : [] as $file) {
            if ('.' !== $file && '..' !== $file && is_dir($path . $file)) {
                $this->clear($path . $file . DIRECTORY_SEPARATOR, $rmdir, $expire);
                if ($rmdir) @rmdir($path . $file);
            } elseif ('.gitignore' != $file && is_file($path . $file)) {
                if ($expire) {
                    if ($this->cacheHasExpired($path . $file)) {
                        @unlink($path . $file);
                    }
                } else {
                    @unlink($path . $file);
                }
            }
        }
    }

    /**
     * 缓存文件是否已过期
     * @param $filename string 文件路径
     * @return boolean
     */
    private function cacheHasExpired(string $filename): bool
    {
        $expire = (int)substr(file_get_contents($filename), 8, 12);
        return 0 != $expire && time() - $expire > filemtime($filename);
    }
}
