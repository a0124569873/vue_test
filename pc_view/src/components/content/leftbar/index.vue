<template>
      <div id="leftbar" style="height: 100%">
        <el-row class="tac">
            <el-col :span="3" style="height: 100%">
                <el-menu
                default-active="1"
                >
                    <el-menu-item index="1" @click="toc()">新闻</el-menu-item>
                    <el-menu-item index="2" @click="tod()">电影</el-menu-item>
                    <el-menu-item index="3" @click="toe()">文章</el-menu-item>
                    <el-submenu index="">
                      <template slot="title">
                        <span>用户设置</span>
                      </template>
                      <el-menu-item index="4" @click="tof()">添加用户</el-menu-item>
                      <el-menu-item index="4" @click="tof()">邮件订阅</el-menu-item>
                    </el-submenu>
                    
                </el-menu>
            </el-col>
            <el-col :span="12">

            ROUTER_NAME: <a id="ta">{{texxtt}}</a>
            <router-view></router-view>

            </el-col>
        </el-row>

    </div>
</template>
