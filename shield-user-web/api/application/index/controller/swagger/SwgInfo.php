<?php

namespace app\index\controller;

class SwgInfo {
 
    /**
     * Swagger v2
     * @SWG\Swagger(
     *      schemes={"http"},
     *      host="user.shield.com",
     *      basePath="/v1",
     *      @SWG\Info(
     *          version="1.0.0",
     *          title="幻盾用户WEB",
     *          description="",
     *      ),
     *      consumes={"application/json"},
     *      produces={"application/json"}
     * )
     */

    /**
     * @SWG\Definition(
     *      definition="Errcode",
     *      type="object",
     *      @SWG\Property(property="10001", type="用户名或密码错误"),
     *      @SWG\Property(property="10002", type="验证码错误"),
     *      @SWG\Property(property="10003", type="该邮箱已注册"),
     *      @SWG\Property(property="10004", type="邮件发送失败"),
     *      @SWG\Property(property="10005", type="链接超时"),
     *      @SWG\Property(property="10006", type="验证码错误"),
     *      @SWG\Property(property="10007", type="该邮箱已设置为安全邮箱"),
     *      @SWG\Property(property="10008", type="该手机号已设置为安全手机"),
     * 
     *      @SWG\Property(property="11000", type="缺少参数"),
     *      @SWG\Property(property="11001", type="邮箱格式错误"),
     *      @SWG\Property(property="11002", type="密码复杂度低"),
     *      @SWG\Property(property="11003", type="手机号码无效"),
     *      @SWG\Property(property="11004", type="QQ号码无效"),
     *      @SWG\Property(property="11005", type="昵称过长或含有特殊字符"),
     *  
     *      @SWG\Property(property="12001", type="需要登录"),
     *
     *      @SWG\Property(property="13001", type="请求数据不存在"),
     *
     *      @SWG\Property(property="14001", type="非法操作"),
     *      @SWG\Property(property="14200", type="操作失败"),
     *      @SWG\Property(property="14003", type="没有权限"),
     *
     *      @SWG\Property(property="20000", type="数据库连接异常"),
     *      @SWG\Property(property="20001", type="数据库操作失败"),
     *      @SWG\Property(property="21000", type="ZK异常"),
     *      @SWG\Property(property="22000", type="ES异常"),
     *
     *  )
     */

    /**
     * @SWG\Definition(
     *      definition="Fail",
     *      type="object",
     *      @SWG\Property(property="errcode", type="integer", example=10001),
     *      @SWG\Property(property="errmsg", type="string", example="params missing")
     *  )
     * 
     * @SWG\Definition(
     *      definition="Success",
     *      type="object",
     *      @SWG\Property(property="errcode", type="integer", example=0),
     *      @SWG\Property(property="errmsg", type="string", example="ok")
     *  )
     *
     */


}