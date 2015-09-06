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

    public static function isDate($cell)
    {
        $cellstyleformat = $cell->getStyle($cell->getCoordinate())->getNumberFormat();
        $formatcode = $cellstyleformat->getFormatCode();
        return preg_match('/^(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy]/i', $formatcode);
    }

    public static function phpDateToObjectDate($date)
    {
        import("Org.Util.PHPExcel");
        return "20" . \PHPExcel_Style_NumberFormat::toFormattedString($date, "Y-m-d H:i:s");
    }

}