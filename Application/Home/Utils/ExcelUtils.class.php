<?php
namespace Home\Utils;
/**
 * Created by IntelliJ IDEA.
 * User: linjiabin
 * Date: 15/8/17
 * Time: 下午1:42
 */
class ExcelUtils
{
    static function excel($excel, $fileName)
    {
        if (!$fileName) {
            $fileName = time();
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
        header('Cache-Control: max-age=0');
//        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
//        $objWriter->save('php://output');
        // Save Excel 2007 file
        $objWriter = new \PHPExcel_Writer_Excel2007($excel);
        $objWriter->save('php://output');
    }
}