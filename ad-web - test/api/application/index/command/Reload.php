<?php
namespace app\index\command;

use app\index\event\Confbuilder;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\DB;

class Reload extends Command
{
    protected function configure()
    {
        $this->setName('reload')->setDescription('reload the config write in shared memory. ');
    }

    protected function execute(Input $input, Output $output)
    {

        $output->writeln('Reload ... ');
        // $this->reloadNetWork();
        $this->buildProtectConfig();
        
    }

    /**
     * 配置网络地址
     * @access protected
     * @param void
     * @return void
     */
    protected function reloadNetWork(){
       
    }

    /**
     * 调用builder构建所有防护配置写入共享内存
     * @access protected
     * @param void
     * @return void
     */
    protected function buildProtectConfig(){
        $builder = new Confbuilder;
        $json_content = $builder->forAll();
        //写入共享内存
        $result = WriteInShm($json_content);
        if($result){

            // exec("uroot fpcmd system_config");
            // exec("uroot fpcmd b_w_list_config");
            // exec("uroot fpcmd server_config");

            $res = "System para";
            while (!strstr($res, "System para")) {
                $res = exec("uroot fpcmd system_config 2>&1");
            }

            $res = "aa";
            while (!empty($res)) {
                $res = exec("uroot fpcmd b_w_list_config 2>&1");
            }

            $res = "aa";
            while (!empty($res)) {
                $res = exec("uroot fpcmd server_config 2>&1");
            }

        }
    }

}

    