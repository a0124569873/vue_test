<?php

namespace app\index\validate;
use think\Validate;

class Protect extends Validate{

    protected $rule = [
        't'        => 'require|checkConfType:1,2,3,4,5,6,7,8',
        'gether'   => 'require|in:0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',
        'page'     => 'integer|gt:0',
        'row'      => 'integer|gt:0',
        'list_type'=> 'require|in:0,1',
        'ip_range' => 'checkIpRange',

        'add4'     => 'require|checkadd4',
        'add5'     => 'require|checkadd5',
        'add6'     => 'require|checkadd6',
        'add7'     => 'require|checkadd7',
        'add8'     => 'require|checkadd8',

        'update1'  => 'require|in:bypass,atk_def',
        'update2'  => 'require|integer',
        'update3'  => 'require|checkupdate3',
        'update4'  => 'require|checkupdate4',
        'update5'  => 'require|checkupdate5',
        'update7'  => 'require|checkupdate7',

        'del4'     => 'require|checkIds',
        'del5'     => 'require|checkIds',
        'del6'     => 'require|checkdel6',
        'del7'     => 'require|checkIpRanges',
        'del8'     => 'require|checkdel8',

        'clear6'   => 'require|checkclear6',
        'clear8'   => 'require|checkclear8',
        
    ];

    protected $message  =   [
        't.require'         =>  '22001',
        't.checkConfType'   =>  '15009',
        'gether.require'    =>  '22001',
        'gether.in'         =>  '15009',
        'page.integer'      =>  '15004',
        'page.gt'           =>  '15004',
        'row.integer'       =>  '15006',
        'row.gt'            =>  '15006',
        'list_type.require' =>  '22001',
        'list_type.in'      =>  '15009',
        'ip_range.checkIpRange' =>  '11016',
        
        'add4.require'      =>  '22001',
        'add5.require'      =>  '22001',
        'add6.require'      =>  '22001',
        'add7.require'      =>  '22001',
        
        'update1.require'   =>  '22001',
        'update1.in'        =>  '15009',
        'update2.require'   =>  '22001',
        'update2.integer'   =>  '11001',
        'update3.require'   =>  '22001',
        'update4.require'   =>  '22001',
        'update5.require'   =>  '22001',
        'update7.require'   =>  '22001',

        'del4.require'      =>  '22001',
        'del4.checkIds'     =>  '11021',
        'del5.require'      =>  '22001',
        'del5.checkIds'     =>  '11021',
        'del6.require'      =>  '22001',
        'del7.require'      =>  '22001',
        'del7.checkIpRanges'=>  '11016',

        'clear6.require'    =>  '22001',
    ];

    protected $scene = [
        'get'               =>  ['t'],
        'get_hostproparam'  =>  ['gether'],
        'get_tcppro'        =>  ['gether'],
        'get_udppro'        =>  ['gether'],
        'get_blackwhitelist'=>  ['page','row','list_type','gether'],
        'get_hostprotect'   =>  ['page','row','ip_range'],
        'get_dnswhitelist'  =>  ['page','row','gether'],

        'add'               =>  ['t'=>'checkConfType:4,5,6,7,8'],
        'add_tcppro'        =>  ['add4'],
        'add_udppro'        =>  ['add5'],
        'add_blackwhitelist'=>  ['add6'],
        'add_hostprotect'   =>  ['add7'],
        'add_dnswhitelist'  =>  ['add8'],
        
        'update'               =>  ['t'=>'checkConfType:1,2,3,4,5,7'],
        'update_syspromodel'   =>  ['update1'],
        'update_sysproparam'   =>  ['update2'],
        'update_hostproparam'  =>  ['update3'],
        'update_tcppro'        =>  ['update4'],
        'update_udppro'        =>  ['update5'],
        'update_hostprotect'   =>  ['update7'],

        'del'               =>  ['t'=>'checkConfType:4,5,6,7,8'],
        'del_tcppro'        =>  ['del4'],
        'del_udppro'        =>  ['del5'],
        'del_blackwhitelist'=>  ['del6'],
        'del_hostprotect'   =>  ['del7'],
        'del_dnswhitelist'  =>  ['del8'],

        'clear'             =>  ['t'=>'checkConfType:6,8'],
        'clear_blackwhitelist'=>  ['clear6'],
        'clear_dnswhitelist' =>  ['clear8'],
        
    ];

    // 验证TCP端口保护添加操作参数
    protected function checkadd4($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 6){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if( $k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15') ){
                return "15011";
            }
            if( ($k == 1 || $k == 2) && !$this->checkPort($i)){
                return '11009';
            }
            if( $k == 3 && !(Validate::is($i,'integer') && Validate::in($i,'0,1')) ){
                return '15009';
            }
            if( $k == 4 && ($i < 0 || !Validate::is($i,'integer')) ){
                return '11001';
            }
            if( $k == 5 && $i != '' ){
                foreach(explode(',', $i) as $item){
                    if(!Validate::in($i,'1,2,3')){
                        return "15012";
                    }
                    continue;
                }
            }
        }
        return true;
    }
    // 验证UDP端口保护添加操作参数
    protected function checkadd5($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 4){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if( $k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15') ){
                return "15011";
            }
            if( ($k == 1 || $k == 2) && !$this->checkPort($i)){
                return '11009';
            }
            if( $k == 3 && !(Validate::is($i,'integer') && Validate::in($i,'0,1')) ){
                return '15009';
            }
            // if( ($k == 4 || $k == 5) && ($i < 0 || !Validate::is($i,'integer')) ){
            //     return '11001';
            // }
            // if( $k == 6 && $i != '' ){
            //     foreach(explode(',', $i) as $item){
            //         if(!in_array($item,[1,2,3,4])){
            //             return "15012";
            //         }
            //         continue;
            //     }
            // }
        }
        return true;
    }
    // 验证黑白名单添加操作参数
    protected function checkadd6($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 4){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if($k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if($k == 1 && !Validate::in($i,'0,1')){
                return "15009";
            }
            if($k == 2 && !$this->checkIpRange($i)){
                return '11016';
            }
            if($k == 3 && strlen($i) > 60){
                return '15001';
            }
        }
        return true;
    }
    // 验证添加防护范围参数
    protected function checkadd7($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 8){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if( $k == 0 && !$this->checkIpRange($i)){
                return '11016';
            }
            if(($k == 1||$k == 2||$k == 3) && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if(($k == 4||$k == 5||$k == 6) && !(empty($i) && $i !='0') && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if($k == 7 && !(Validate::is($i,'integer') && Validate::in($i,'1,2,3')) ){
                return "15012";
            }
        }
        return true;
    }
    // 验证DNS白名单添加操作参数
    protected function checkadd8($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 3){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if($k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if($k == 1 && !$this->checkIpRange($i)){
                return '11016';
            }
            if($k == 2 && strlen($i) > 60){
                return '15001';
            }
        }
        return true;
    }
    // 验证更新全局防护参数
    protected function checkupdate3($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 4){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if($k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if( !Validate::is($i,'integer') ){
                return '11001';
            }
            if( $i < 0 ){
                return '11001';
            }
        }
        return true;
    }
    // 验证TCP端口保护更新操作参数
    protected function checkupdate4($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 6){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if( $k == 0 && ($i < 0 || !Validate::is($i,'integer')) ){
                return "11021";
            }
            if( ($k == 1 || $k == 2) && !$this->checkPort($i)){
                return '11009';
            }
            if( $k == 3 && !(Validate::is($i,'integer') && Validate::in($i,'0,1')) ){
                return '15009';
            }
            if( $k == 4 && ($i < 0 || !Validate::is($i,'integer')) ){
                return '11001';
            }
            if( $k == 5 && $i != '' ){
                foreach(explode(',', $i) as $item){
                    if(!Validate::in($i,'1,2,3')){
                        return "15012";
                    }
                    continue;
                }
            }
        }
        return true;
    }
    // 验证UDP端口保护更新操作参数
    protected function checkupdate5($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 4){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if( $k == 0 && ($i < 0 || !Validate::is($i,'integer')) ){
                return "11021";
            }
            if( ($k == 1 || $k == 2) && !$this->checkPort($i)){
                return '11009';
            }
            if( $k == 3 && !(Validate::is($i,'integer') && Validate::in($i,'0,1')) ){
                return '15009';
            }
        }
        return true;
    }
    // 验证防护范围更新操作参数
    protected function checkupdate7($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 8){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if($k == 0 && ($i < 0 || !Validate::is($i,'integer'))){
                return "11021";
            }
            if(($k == 1||$k == 2||$k == 3) && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if(($k == 4||$k == 5||$k == 6) && !(empty($i) && $i !='0') && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if($k == 7 && !(Validate::is($i,'integer') && Validate::in($i,'1,2,3')) ){
                return "15012";
            }
        }
        return true;
    }
    // 验证黑白名单删除操作参数
    protected function checkdel6($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 3){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if($k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if($k == 1 && !Validate::in($i,'0,1')){
                return "15009";
            }
            if($k == 2 && !$this->checkMutliIpRanges($i)){
                return '11016';
            }
        }
        return true;
    }
    // 验证DNS白名单删除操作参数
    protected function checkdel8($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 2){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if($k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if($k == 1 && !$this->checkMutliIpRanges($i)){
                return '11016';
            }
        }
        return true;
    }
    // 验证黑白名单清空操作参数
    protected function checkclear6($value, $rule, $data){
        $conf_arr = explode("|", $value);
        if(count($conf_arr) != 2){
            return "22001";
        }
        foreach($conf_arr as $k => $i){
            if($k == 0 && !Validate::in($i,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
                return "15011";
            }
            if($k == 1 && !Validate::in($i,'0,1')){
                return "15009";
            }
        }
        return true;
    }
    // 验证黑白名单清空操作参数
    protected function checkclear8($value, $rule, $data){
        if(!Validate::in($value,'0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15')){
            return "15011";
        }
        return true;
    }
    //验证多个类型|隔开
    protected function checkConfType($value, $rule){
        $valueArr = explode('|', $value);
        $ruleArr = explode(',', $rule);
        
        $resultArr = array_filter($valueArr, function ($item) use ($ruleArr) {
            return in_array($item, $ruleArr);
        });

        return count($resultArr) == count($valueArr);
    }
    //验证ip范围 ip掩码或ip
    protected function checkIpRange($value){
        return Validate::is($value,'ip') || CheckIpRange($value) || CheckIpMask($value);
    }
    // 验证端口
    protected function checkPort($value){
        return is_numeric($value) && floor($value) == $value && $value >= 0 && $value <= 65535 ? true : false;
    }
    // 验证多个id
    protected function checkIds($value){
        $ids_arr = explode('|', $value);
        $valid_arr = array_filter($ids_arr,function($i){
            return Validate::is($i,'integer') && $i > 0;
        });

        return count($ids_arr) == count($valid_arr);
    }
    // 验证多个ip范围 ip 或ip掩码 |隔开
    protected function checkIpRanges($value){
        $ips_arr = explode('|', $value);
        $valid_arr = array_filter($ips_arr,function($i){
            return $this->checkIpRange($i);
        });

        return count($ips_arr) == count($valid_arr);
    }

    // 验证多个ip范围 ip 或ip掩码 ,隔开
    protected function checkMutliIpRanges($value){
        $ips_arr = explode(',', $value);
        $valid_arr = array_filter($ips_arr,function($i){
            return $this->checkIpRange($i);
        });

        return count($ips_arr) == count($valid_arr);
    }
}

