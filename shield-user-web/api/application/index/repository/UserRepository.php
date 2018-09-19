<?php

namespace app\index\repository;

use app\index\model\UserModel;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new UserModel();
    }

    /**
     * 根据用户邮箱查找用户
     *
     * @param String $email
     * @return Array
     */
    public function selectUserById($email)
    {
        $data = $this->model->esGetById($email);
        return $data;
    }

    /**
     * 创建用户
     *
     * @param Array $user_info
     * @return Boolean
     */
    public function insertUser($user_info)
    {
        $id = $user_info['email'];
        $data = $this->model->esAdd($user_info, $id);
        if ($data === false) {
            return false;
        }
        return $data["result"] == 'created' ? true : false;
    }

    /**
     * 更新用户信息
     *
     * @param Array $user_info
     * @return Boolean
     */
    public function updateUser($user_info, $id)
    {
        $data = $this->model->esUpdateById($user_info, $id);
        if ($data === false) {
            return false;
        }
        return $data["result"] == 'updated' ? true : false;
    }

    /**
     * 根据某列搜索用户信息
     * 
     */
    public function searchByFilter($filter){
        $filter = [
            "query"=>[
                "match"=>$filter
            ]
        ];
        $data = $this->model->esSearch($filter);
        return $data;
    }

}
