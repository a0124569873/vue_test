<?php

class Manage{

    /**
     *  @SWG\Definition(
     *      definition="Manageinfo",
     *      type="object",
     *      allOf={
     *          @SWG\Schema(ref="#/definitions/Success"),
     *          @SWG\Schema(
     *              @SWG\Property(property="all_domain_counts", type="integer", example=20, description="共添加域名个数"),
     *              @SWG\Property(property="joined_site_counts", type="integer", example=18, description="网站防护个数"),
     *              @SWG\Property(property="joined_port_counts", type="integer", example=10, description="非网站防护个数"),
     *              @SWG\Property(property="account", type="float", example=1888.88, description="账户余额"),
     *              @SWG\Property(property="user_email", type="string", example="test@veda.com", description="用户邮箱")
     *          )
     *      }
     *  )
     */

}