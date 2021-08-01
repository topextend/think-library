<?php
// -----------------------------------------------------------------------
// |@Author       : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Date         : 2021-08-01 11:23:21
// |@----------------------------------------------------------------------
// |@LastEditTime : 2021-08-01 11:23:37
// |@----------------------------------------------------------------------
// |@LastEditors  : Jarmin <jarmin@ladmin.cn>
// |@----------------------------------------------------------------------
// |@Description  : 
// |@----------------------------------------------------------------------
// |@FilePath     : Auth.php
// |@----------------------------------------------------------------------
// |@Copyright (c) 2021 http://www.ladmin.cn   All rights reserved. 
// -----------------------------------------------------------------------
declare (strict_types=1);

namespace think\admin\extend;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use think\admin\Exception;
use think\exception\HttpResponseException;

/**
 * 导出 CSV 文件扩展
 * Class ExcelExtend
 * @package think\admin\extend
 */
class ExcelExtend
{

    /**
     * 设置写入 CSV 文件头部
     * @param string $name 导出文件名称
     * @param array $headers 表格头部(一维数组)
     */
    public static function header(string $name, array $headers): void
    {
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=" . iconv('utf-8', 'gbk//TRANSLIT', $name));
        $handle = fopen('php://output', 'w');
        foreach ($headers as $key => $value) {
            $headers[$key] = iconv("utf-8", "gbk//TRANSLIT", $value);
        }
        fputcsv($handle, $headers);
        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    /**
     * 设置写入CSV文件内容
     * @param array $list 数据列表(二维数组)
     * @param array $rules 数据规则(一维数组)
     */
    public static function body(array $list, array $rules): void
    {
        $handle = fopen('php://output', 'w');
        foreach ($list as $data) {
            $rows = [];
            foreach ($rules as $rule) {
                $rows[] = static::parseKeyDotValue($data, $rule);
            }
            fputcsv($handle, $rows);
        }
        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    /**
     * 根据数组key查询(可带点规则)
     * @param array $data 数据
     * @param string $rule 规则，如: order.order_no
     * @return string
     */
    public static function parseKeyDotValue(array $data, string $rule): string
    {
        [$temp, $attr] = [$data, explode('.', trim($rule, '.'))];
        while ($key = array_shift($attr)) $temp = $temp[$key] ?? $temp;
        return (is_string($temp) || is_numeric($temp)) ? @iconv('utf-8', 'gbk//TRANSLIT', "{$temp}") : '';
    }

    /**
     * 从数据库导出数据到表格
     * @param sring $title 首行标题内容
     * @param array $column        第二行列头标题
     * @param array $setWidth      第二行列头宽度
     * @param array $list          从数据库获取表格内容
     * @param array $keys          要获取的内容键名
     * @param array $lastRow       最后一行设置
     * @param string $filename     导出的文件名
     */
    public static function export(string $title, array $column, array $setWidth, array $list, array $keys, array $lastRow=[], string $filename='')
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $count = count($column);
        // 合并首行单元格
        $worksheet->mergeCells(chr(65).'1:'.chr($count+64).'1');
        $styleArray = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,],
        ];
        // 设置首行单元格内容
        $worksheet->setTitle($title);
        $worksheet->setCellValueByColumnAndRow(1, 1, $title);
        // 设置单元格样式
        $worksheet->getStyle(chr(65).'1')->applyFromArray($styleArray)->getFont()->setSize(18);
        $worksheet->getStyle(chr(65).'2:'.chr($count+64).'2')->applyFromArray($styleArray)->getFont()->setSize(12);
        // 设置列头内容
        foreach ($column as $key => $value) $worksheet->setCellValueByColumnAndRow($key+1, 2, $value);
        // 设置列头格式
        foreach ($setWidth as $k => $v) $worksheet->getColumnDimension(chr($k+65))->setWidth(intval($v));
        // 从数据库获取表格内容
        $len = count($list);
        $j = 0;
        for ($i=0; $i < $len; $i++){
            $j = $i + 3; //从表格第3行开始
            foreach ($keys as $kk => $vv){
                $worksheet->setCellValueByColumnAndRow($kk+1, $j, $list[$i][$vv]);
            }
        }
        $total_jzInfo = $len + 2;
        $styleArrayBody = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '666666'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        // 最后一行计算值
        if (!empty($lastRow)) {
            // 合并最后一行
            $worksheet->mergeCells(chr(65).($len+3).':'.chr(count($lastRow)+64).($len+3));
            foreach ($lastRow as $item)
            {
                $worksheet->setCellValueByColumnAndRow(array_keys($lastRow,$item)[0], $len+3, $item);
            }
            $total_jzInfo = $len + 3;
        }
        // 添加所有边框/居中
        $worksheet->getStyle(chr(65).'1:'.chr($count+64).$total_jzInfo)->applyFromArray($styleArrayBody);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition:attachment;filename={$filename}.xlsx");
        header('Cache-Control: max-age=0');//禁止缓存
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    public static function import()
    {
        //
    }
}