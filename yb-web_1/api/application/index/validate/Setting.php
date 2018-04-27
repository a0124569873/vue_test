<?php
namespace app\index\validate;
use think\Validate;

class Setting extends Validate{


    protected $rule = [
        'type'      => 'require|checkConfType:1,2',
        'page'      => 'integer|gt:0',
        'row'       => 'integer|gt:0',
        'type1'     => 'require|check1',
    ];

    protected $message = [
        'type.checkConfType'=>  '15009',
        'page.integer'      =>  '15004',
        'page.gt'           =>  '15004',
        'row.integer'       =>  '15006',
        'row.gt'            =>  '15006',
        'type1.require'     =>  '22001',
        'type1.check1'      =>  "11018",
        'def_time.require'  =>  '22001',
        'def_time.integer'  =>  '11015',
        'def_time.gt'       =>  '11015',
        'con_time.require'  =>  '22001',
        'con_time.integer'  =>  '11015',
        'con_time.gt'       =>  '11015',
        'server.require'    =>  '22001',
        'server.ip'         =>  '11006',
        'username.require'  =>  '22001',
        'password.require'  =>  '22001',
        'addtype4.require'  =>  '22001',
        'updatetype4.require' =>  '22001',
        'type2.require'     =>  '22001',
        'total_th.require'  =>  '22001',
        'total_th.integer'  =>  '15007',
        'total_th.gt'       =>  '15007',
        'sample.require'    =>  '22001',
        'sample.integer'    =>  '15010',
        'sample.gt'         =>  '15010',
        'sample.elt'        =>  '15010',
        'wlist.require'     =>  '22001',
        'ids.require'       =>  '22001',
        'ids.multiInteger'  =>  '11010',
        'port.require'      =>  '22001',
        'email.require'     =>  '22001',
        'email.email'       =>  '11004',
        'iprange.require'   =>  '22001',
        'type10.require'    =>  '22001',
        
    ];

    protected $scene = [
        'get'           => ['type'],
        'set'           => ['type'],
        'check_add_vpn'=> ['type1'],
        'del'           => ['type'],     
        
    ];

    /**
     * 验证传入的配置类型
     * @access protected
     * @param string $value 验证数据
     * @param string $rule 验证规则
     * @return bool
     */
    protected function checkConfType($value, $rule){
        $valueArr = explode('|', $value);
        $ruleArr = explode(',', $rule);
        
        $resultArr = array_filter($valueArr, function ($item) use ($ruleArr) {
            return in_array($item, $ruleArr);
        });

        return count($resultArr) == count($valueArr);
    }

    protected function check1($value1, $rule, $data){

        foreach ($value1 as $key => $value) {
            $valueArr = explode('|', $value);
            if (count($valueArr) == 5){
                if (!Validate::is($valueArr[0],'ip')){
                    Error("11016","ip error");
                }

                if (!strstr($valueArr[4], "/") || !$this->CheckIpMask($valueArr[4])) {
                    Error("11016","error ip_range ip error");
                }

            }elseif (count($valueArr) == 6) {
                if (!Validate::is($valueArr[1],'ip')){
                    Error("11016","ip error");
                }

                if (!strstr($valueArr[5], "/") || !$this->CheckIpMask($valueArr[5])) {
                    Error("11016","error ip_range ip error");
                }

            }else{
                Error("22001","param num error");
            }

        }
        return true;

    }

    // 验证IP/MASK
    function CheckIpMask($ip){
        $ip_mask = explode('/', $ip);
        return count($ip_mask)==2 && ip2long($ip_mask[0]) && is_numeric($ip_mask[1]) && is_int((int)$ip_mask[1]) && $ip_mask[1] >= 1 && $ip_mask[1] <= 32;
    }

}