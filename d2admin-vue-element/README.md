<p align="center">
  <img width="550" src="http://fairyever.qiniudn.com/github-banner.png">
</p>

基于 `vue.js` 和 `ElementUI` 的管理系统前端解决方案

<p>
  <a href="https://github.com/vuejs/vue">
    <img src="https://img.shields.io/badge/vue-2.5.2-brightgreen.svg" alt="vue">
  </a>
  <a href="https://github.com/ElemeFE/element">
    <img src="https://img.shields.io/badge/element--ui-2.0.11-brightgreen.svg" alt="element-ui">
  </a>
  <a href="https://github.com/vuejs/vuex/">
    <img src="https://img.shields.io/badge/vuex-3.0.1-brightgreen.svg" alt="vuex">
  </a>
  <a href="https://github.com/axios/axios">
    <img src="https://img.shields.io/badge/axios-0.17.1-brightgreen.svg" alt="axios">
  </a>
</p>

欢迎大家一起维护

[线上预览地址 d2admin.fairyever.com](http://d2admin.fairyever.com/)

为了观看体验，预览截图放在最后

> 此项目适合作为模板使用。因为现在集成了很多的插件和组件，首次加载会占用较多的时间，虽然已经做了首屏加载动画，还是建议您在发布的时候一定要删除没有用到的代码。比如项目没有用到图表，最好将集成的图表库以及封装的图表组件删除。

## 功能

* 首屏加载等待动画 避免首次加载白屏尴尬
* 简约主题
* 每个插件和组件都配有介绍文档
* 图片资源 sketch 源文件（ 可以在这个文件内重新生成所有图片资源 ）
* 登陆和注销
* 根据路由自动生成菜单
* 可折叠侧边栏
* 多国语言支持
* 富文本编辑器
* Markdown 编辑器
* 全屏功能
* Fontawesome 图标库
* Fontawesome 图标选择器（组件）
* 自动引入下载的 SVG 图标
* 前端假数据支持（ mock ）
* 集成 G2 图表
* 图表自适应可拖拽大小的卡片容器（示例）
* 简化剪贴板操作
* 简化Cookie操作
* 时间计算工具
* 导入 Excel （ xlsx 格式 + CSV 格式 ）
* 数据导出 Excel （ xlsx 格式 + CSV 格式 ）
* 数据导出文本
* 数字动画
* 可拖拽调整大小的切分布局
* 可拖拽调整大小和位置的网格布局
* 提供三种页面容器组件（正常卡片，隐形容器，填满页面）
* 代码高亮显示
* 加载并解析 markdown 文件
* GitHub 样式的 markdown 显示组件
* markdown 内代码高亮
* 为 markdown 扩展了百度云链接解析和优化显示

## 目录结构

```
├─ build // 打包设置
├─ config // 发布设置
├─ preview-image // github 介绍页面使用的插图 以后可能去掉
├─ src
│  ├─ assets
│  │  ├─ icons // 存放 svg 图标
│  │  ├─ image // 图片
│  │  ├─ library // 库
│  │  └─ style // 公用样式
│  ├─ components
│  │  ├─ charts // 封装图表组件
│  │  ├─ core // 核心组件
│  │  ├─ demo // 只会在示例页面中使用的组件
│  ├─ i18n // 多国语言配置
│  ├─ mock // mock 数据设置
│  ├─ pages
│  │  ├─ core // 系统页面
│  │  └─ demo // 演示页面
│  │     ├─ business // 业务页面示例
│  │     ├─ chart // 图表
│  │     ├─ components // 组件
│  │     └─ plugins // 插件
│  ├─ plugin // 插件
│  ├─ router // 路由
│  │  ├─ invisible
│  │  └─ menu
│  ├─ store // 全局状态
│  ├─ utils // 通用工具
│  ├─ App.vue
│  └─ main.js // 入口文件
├─ static
├─ .babelrc
├─ .editorconfig
├─ .eslintignore
├─ .eslintrc.js
├─ .gitignore
├─ .postcssrc.js
├─ LICENSE
├─ README.md
├─ design.sketch
├─ index.html
├─ package-lock.json
└─ package.json
```

## 使用

安装依赖

```
npm i
```

运行

```
npm run dev
```

在以下环境测试可用

```
➜  ~ npm -v
5.6.0
➜  ~ node -v
v8.11.1
➜  ~ nrm -V
1.0.2
➜  ~ nrm ls
  npm ---- https://registry.npmjs.org/
  cnpm --- http://r.cnpmjs.org/
* taobao - https://registry.npm.taobao.org/
  nj ----- https://registry.nodejitsu.com/
  rednpm - http://registry.mirror.cqupt.edu.cn/
  npmMirror  https://skimdb.npmjs.com/registry/
  edunpm - http://registry.enpmjs.org/
```

> 不建议使用 `cnpm`
      
## 预览

登陆

![Snip20180526_1](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_1.png)

主界面

![Snip20180526_2](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_2.png)

模拟数据

![Snip20180526_3](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_3.png)

可拖拽卡片

![Snip20180526_4](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_4.png)

可拖拽调节的布局

![Snip20180526_5](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_5.png)

多国语言支持

![Snip20180526_6](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_6.png)

封装导入 `csv` `xlsx`
封装导出 `csv` `xlsx` 以及纯文本

![Snip20180526_7](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_7.png)

![Snip20180526_8](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_8.png)

四种页面容器组件

![Snip20180526_9](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_9.png)

集成富文本编辑器

![Snip20180526_10](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_10.png)

集成 markdown 编辑器

![Snip20180526_11](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_11.png)

集成图标库

![Snip20180526_12](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_12.png)

封装图标组件

![Snip20180526_13](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_13.png)

支持自动导入文件夹下的 svg 图标，并通过组件使用

![Snip20180526_14](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_14.png)

图标选择器组件

![Snip20180526_15](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_15.png)

集成 markdown 渲染功能
并且优化了百度云分享链接在渲染结果的显示

![Snip20180526_16](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_16.png)

集成数字动画插件

![Snip20180526_17](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_17.png)

代码高亮显示

![Snip20180526_18](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_18.png)

集成图表

![Snip20180526_19](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_19.png)

支持图表拖拽调整大小，以及拖放位置

![Snip20180526_20](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_20.png)

单独图表示例

![Snip20180526_21](https://raw.githubusercontent.com/FairyEver/d2admin-vue-element/master/preview-image/Snip20180526_21.png)


