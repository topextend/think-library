<?php
// ------------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-15 17:48:44
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : Database.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// ------------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\command;

use think\admin\Command;
use think\admin\Exception;
use think\admin\service\SystemService;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 数据库修复优化指令
 * Class Database
 * @package think\admin\command
 */
class Database extends Command
{
    /**
     * 指令任务配置
     */
    public function configure()
    {
        $this->setName('xadmin:database');
        $this->addArgument('action', Argument::OPTIONAL, 'repair|optimize', 'optimize');
        $this->setDescription('Database Optimize and Repair for ThinkAdmin');
    }

    /**
     * 任务执行入口
     * @param Input $input
     * @param Output $output
     * @return void
     */
    protected function execute(Input $input, Output $output): void
    {
        $method = $input->getArgument('action');
        if (in_array($method, ['repair', 'optimize'])) {
            $this->{"_{$method}"}();
        } else {
            $this->output->error("Wrong operation, currently allow repair|optimize");
        }
    }

    /**
     * 修复所有数据表
     * @throws Exception
     */
    protected function _repair(): void
    {
        $this->setQueueProgress("正在获取需要修复的数据表", '0');
        [$tables, $total, $count] = SystemService::instance()->getTables();
        $this->setQueueProgress("总共需要修复 {$total} 张数据表", '0');
        foreach ($tables as $table) {
            $this->setQueueMessage($total, ++$count, "正在修复数据表 {$table}");
            $this->app->db->query("REPAIR TABLE `{$table}`");
            $this->setQueueMessage($total, $count, "完成修复数据表 {$table}", 1);
        }
        $this->setQueueSuccess("已完成对 {$total} 张数据表修复操作");
    }

    /**
     * 优化所有数据表
     * @throws Exception
     */
    protected function _optimize(): void
    {
        $this->setQueueProgress("正在获取需要优化的数据表", '0');
        [$tables, $total, $count] = SystemService::instance()->getTables();
        $this->setQueueProgress("总共需要优化 {$total} 张数据表", '0');
        foreach ($tables as $table) {
            $this->setQueueMessage($total, ++$count, "正在优化数据表 {$table}");
            $this->app->db->query("OPTIMIZE TABLE `{$table}`");
            $this->setQueueMessage($total, $count, "完成优化数据表 {$table}", 1);
        }
        $this->setQueueSuccess("已完成对 {$total} 张数据表优化操作");
    }
}