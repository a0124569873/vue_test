<?php
namespace app\index\event;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * 解析日志数据并构建excel下载
 */
class Excelbuilder extends Controller {

    /**
     * 导出excel
     * @param $report_type 日志类型
     * @param $content 日志数据
     * @param $file_name 日志文件名
     */
    public function exportExcel($report_type, $content, $file_name){
        switch($report_type){
            case 'atteck':
                $sheet_title = "攻击统计";
                break;
            case 'flux':
                $sheet_title = "流量统计";
                break;
            case 'connect':
                $sheet_title = "连接统计";
                break;
            case 'clean':
                $sheet_title = "清洗日志";
                break;
            default:
                $sheet_title = "VEDA";
                break;
        }

        $objPHPExcel = $this->_loadPhpExcel();
        $this->_setExcelProperty($objPHPExcel);
        $this->_writeInExcel($objPHPExcel, $report_type, $content, $sheet=0, $sheet_title);
        $this->_saveExcel($objPHPExcel, $file_name);
    }
    
    // 引入phpexcel类
    private function _loadPhpExcel(){
        vendor("phpoffice.phpexcel.Classes.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        return $objPHPExcel;
    }

    // 设置excel属性
    private function _setExcelProperty($objPHPExcel){
        // 创建人
        $objPHPExcel->getProperties()->setCreator("vedasec");
        // 最后修改人
        $objPHPExcel->getProperties()->setLastModifiedBy("vedasec");
        // 标题
        $objPHPExcel->getProperties()->setTitle("VEDA Report Document");
        // 题目
        $objPHPExcel->getProperties()->setSubject("VEDA Report Document");
        // 描述
        $objPHPExcel->getProperties()->setDescription("This document for VEDA Reports");
    }

    // 写入内容
    private function _writeInExcel($objPHPExcel, $report_type, $content, $sheet, $sheet_title){
        // 设置当前的sheet
        $objPHPExcel->setActiveSheetIndex($sheet);
        // 设置sheet的name
        $objPHPExcel->getActiveSheet()->setTitle($sheet_title);
        // 设置单元格的值
        switch($report_type){
            case 'atteck':
                $this->_structAtteck($objPHPExcel, $content);
                break;
            case 'flux':
                $this->_structFlux($objPHPExcel, $content);
                break;
            case 'connect':
                $this->_structConnect($objPHPExcel, $content);
                break;
            case 'clean':
                $this->_structClean($objPHPExcel, $content);
                break;
            default:
                #...
                break;
        }
    }

    // 保存文件
    private function _saveExcel($objPHPExcel, $filename){
        // 清除缓冲区,避免乱码
        ob_end_clean(); 

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H_i_s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        //设置保存版本格式
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);// xlsx
        $objWriter->save('php://output');
        exit; //必须 终止
    }

    // 攻击统计日志格式解析
    private function _structAtteck($objPHPExcel, $datas){
        // 取可用sheet
        $activeSheet = $objPHPExcel->getActiveSheet();
        // 确定列数
        $columns = ["A","B","C","D","E","F"];
        // 确定标题
        $head = ["序号","目标主机IP","目标主机端口","开始时间","结束时间","攻击类型"];
        // 所有单元格默认高度
        $activeSheet->getDefaultRowDimension()->setRowHeight(15);
        // 设置宽width
        $activeSheet->getColumnDimension('A')->setWidth(5);
        $activeSheet->getColumnDimension('B')->setWidth(11);
        $activeSheet->getColumnDimension('C')->setWidth(14);
        $activeSheet->getColumnDimension('D')->setWidth(19);
        $activeSheet->getColumnDimension('E')->setWidth(19);
        $activeSheet->getColumnDimension('F')->setWidth(10);
        // 构建头部
        foreach($columns as $key => $col){
            // 头部标题样式
            $activeSheet->getStyle($col)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); // 垂直居中
            $activeSheet->getStyle($col)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 水平居中
            // 头部标题
            $activeSheet->setCellValue($col.'1', $head[$key]);
        }
        
        // 写入日志
        foreach($datas as $num => $h){
            $activeSheet->setCellValue("A".($num+2), $num+1);
            $activeSheet->setCellValue("B".($num+2), $h['target_ip']);
            $activeSheet->setCellValue("C".($num+2), $h['target_port']);
            $activeSheet->setCellValue("D".($num+2), $h['start_time']);
            $activeSheet->setCellValue("E".($num+2), $h['end_time']);
            $activeSheet->setCellValue("F".($num+2), $h['attack_type'] == "1" ? "SYN Flood" : ($h['attack_type'] == "1" ? "UDP Flood" : "CC Attack"));
        }
    }

    // 流量日志格式解析
    private function _structFlux($objPHPExcel, $datas){
        // 取可用sheet
        $activeSheet = $objPHPExcel->getActiveSheet();
        // 确定列数
        $columns = ["A","B","C","D","E","F","G","H","I"];
        // 所有单元格默认高度
        $activeSheet->getDefaultRowDimension()->setRowHeight(15);
        // 设置宽width
        $activeSheet->getColumnDimension('A')->setWidth(20);
        // 构建头部 合并单元格
        $activeSheet->mergeCells('A1:A3');
        $activeSheet->mergeCells('B1:E1');
        $activeSheet->mergeCells('F1:I1');
        $activeSheet->mergeCells('B2:C2');
        $activeSheet->mergeCells('D2:E2');
        $activeSheet->mergeCells('F2:G2');
        $activeSheet->mergeCells('H2:I2');
        // 内容样式
        foreach(["A","B","C","D","E","F","G","H","I"] as $b){
            $activeSheet->getStyle($b)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeSheet->getStyle($b)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        // 头部标题样式
        foreach(["A1","B1","F1","B2","D2","F2","H2","B3","C3","D3","E3","F3","G3","H3","I3"] as $b){
            $activeSheet->getStyle($b)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeSheet->getStyle($b)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        // 头部
        $activeSheet->setCellValue("A1", "统计区间");
        $activeSheet->setCellValue("B1", "最大值");
        $activeSheet->setCellValue("F1", "平均值");
        $activeSheet->setCellValue("B2", "输入");
        $activeSheet->setCellValue("D2", "滤后输入");
        $activeSheet->setCellValue("F2", "输入");
        $activeSheet->setCellValue("H2", "滤后输入");
        $activeSheet->setCellValue("B3", "bps");
        $activeSheet->setCellValue("C3", "pps");
        $activeSheet->setCellValue("D3", "bps");
        $activeSheet->setCellValue("E3", "pps");
        $activeSheet->setCellValue("F3", "bps");
        $activeSheet->setCellValue("G3", "pps");
        $activeSheet->setCellValue("H3", "bps");
        $activeSheet->setCellValue("I3", "pps");
        // 写入日志
        foreach($datas as $num => $h){
            $activeSheet->setCellValue("A".($num+4), $h['time']);
            $activeSheet->setCellValue("B".($num+4), $h['flux']['in_max_bps']);
            $activeSheet->setCellValue("C".($num+4), $h['flux']['in_max_pps']);
            $activeSheet->setCellValue("D".($num+4), $h['flux']['in_max_bps_after_clean']);
            $activeSheet->setCellValue("E".($num+4), $h['flux']['in_max_pps_after_clean']);
            $activeSheet->setCellValue("F".($num+4), $h['flux']['in_avg_bps']);
            $activeSheet->setCellValue("G".($num+4), $h['flux']['in_avg_pps']);
            $activeSheet->setCellValue("H".($num+4), $h['flux']['in_avg_bps_after_clean']);
            $activeSheet->setCellValue("I".($num+4), $h['flux']['in_avg_pps_after_clean']);
        }
    }

    // 连接日志格式解析
    private function _structConnect($objPHPExcel, $datas){
        // 取可用sheet
        $activeSheet = $objPHPExcel->getActiveSheet();
        // 确定列数
        $columns = ["A","B","C","D","E","F","G"];
        // 所有单元格默认高度
        $activeSheet->getDefaultRowDimension()->setRowHeight(15);
        // 设置宽width
        $activeSheet->getColumnDimension('A')->setWidth(20);
        // 构建头部 合并单元格
        $activeSheet->mergeCells('A1:A2');
        $activeSheet->mergeCells('B1:D1');
        $activeSheet->mergeCells('E1:G1');
        // 内容样式
        foreach(["A","B","C","D","E","F","G"] as $b){
            $activeSheet->getStyle($b)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeSheet->getStyle($b)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        // 头部标题样式
        foreach(["A1","B1","E1","B2","C2","D2","E2","F2","G2"] as $b){
            $activeSheet->getStyle($b)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeSheet->getStyle($b)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        // 头部
        $activeSheet->setCellValue("A1", "统计区间");
        $activeSheet->setCellValue("B1", "最大值");
        $activeSheet->setCellValue("E1", "平均值");
        $activeSheet->setCellValue("B2", "TCP in");
        $activeSheet->setCellValue("C2", "TCP out");
        $activeSheet->setCellValue("D2", "UDP");
        $activeSheet->setCellValue("E2", "TCP in");
        $activeSheet->setCellValue("F2", "TCP out");
        $activeSheet->setCellValue("G2", "UDP");
        // 写入日志
        foreach($datas as $num => $h){
            $activeSheet->setCellValue("A".($num+3), $h['time']);
            $activeSheet->setCellValue("B".($num+3), $h['flux']['tcp_max_conn_in']);
            $activeSheet->setCellValue("C".($num+3), $h['flux']['tcp_max_conn_out']);
            $activeSheet->setCellValue("D".($num+3), $h['flux']['udp_max_conn']);
            $activeSheet->setCellValue("E".($num+3), $h['flux']['tcp_avg_conn_in']);
            $activeSheet->setCellValue("F".($num+3), $h['flux']['tcp_avg_conn_out']);
            $activeSheet->setCellValue("G".($num+3), $h['flux']['udp_avg_conn']);
        }
    }

    // 清洗日志格式解析
    private function _structClean($objPHPExcel, $datas){
        // 取可用sheet
        $activeSheet = $objPHPExcel->getActiveSheet();
        // 确定列数
        $columns = ["A","B","C","D","E"];
        // 确定标题
        $head = ["序号","时间","本地地址","远程地址","攻击类型"];
        // 所有单元格默认高度
        $activeSheet->getDefaultRowDimension()->setRowHeight(15);
        // 设置宽width
        $activeSheet->getColumnDimension('A')->setWidth(5);
        $activeSheet->getColumnDimension('B')->setWidth(21);
        $activeSheet->getColumnDimension('C')->setWidth(17);
        $activeSheet->getColumnDimension('D')->setWidth(17);
        $activeSheet->getColumnDimension('E')->setWidth(17);
        // 构建头部
        foreach($columns as $key => $col){
            // 居中
            $activeSheet->getStyle($col)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $activeSheet->getStyle($col)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            // 头部标题
            $activeSheet->setCellValue($col.'1', $head[$key]);
        }
        // 写入日志
        foreach($datas as $num => $h){
            $activeSheet->setCellValue("A".($num+2), $num+1);
            $activeSheet->setCellValue("B".($num+2), $h['time']);
            $activeSheet->setCellValue("C".($num+2), $h['target_ip']);
            $activeSheet->setCellValue("D".($num+2), $h['attack_ip']);
            $activeSheet->setCellValue("E".($num+2), $h['attack_type'] == "1" ? "SYN Flood" : ($h['attack_type'] == "2" ? "UDP Flood" : "CC Attack"));
        }
    }
    
}