<?php 

/**
 * @SWG\Definition(
 *      definition="Loginfo",
 *      type="object",
 *      allOf={
 *          @SWG\Schema(ref="#/definitions/Success"),
 *          @SWG\Schema(
 *              @SWG\Property(property="is_login", type="boolean", example=true),
 *              @SWG\Property(property="user_email", type="string", example="test@veda.com")
 *          )
 *      }
 *  )
 * 
 *  @SWG\Definition(
 *      definition="Userinfo",
 *      type="object",
 *      allOf={
 *          @SWG\Schema(ref="#/definitions/Success"),
 *          @SWG\Schema(
 *              @SWG\Property(property="email", type="string", example="test@veda.com", description="登陆邮箱"),
 *              @SWG\Property(property="mobile", type="string", example="135********", description="手机号"),
 *              @SWG\Property(property="mobile_verify_status", type="integer", example=0, description="安全手机：0：未验证，1：已验证"),
 *              @SWG\Property(property="safe_email", type="string", example="test@veda.com", description="安全邮箱"),
 *              @SWG\Property(property="email_verify_status", type="integer", example=0, description="安全邮箱：0：未验证，1：已验证"),
 *              @SWG\Property(property="id_number", type="string", example="410****************", description="身份证"),
 *              @SWG\Property(property="real_name", type="string", example="sy", description="真实姓名"),
 *              @SWG\Property(property="id_verify_status", type="integer", example=1, description="0：未实名，1：待审核，2：已认证"),
 *              @SWG\Property(property="account", type="integer", example=1000, description="账户余额")
 *          )
 *      }
 *  )
 * 
 * 
 */
