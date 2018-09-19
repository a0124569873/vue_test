# 前言
初学vue2.0也有一两个月的心想想做个小项目来测试下我的水平，这不撸起袖子就是干，看了vivo移动商城挺简洁的就仿了个vivo商城，之所以选择vivo商城，是因为商城涉及到购物车操作、订单管理、其次就是网上也有很多的案例但都是音乐播放器，外卖手机端，千篇一律，所以我就选择了商城.

该项目是由(vue2.0+vuex+vue-router+vue-resource+webpack)完成的，后期会用axios代替vue-resource 因为是第一次做手机端有些地方做的还不是那么完美还存在一些小BUG，不过后期我会努力完善的.

写完这个项目我学到了很多收获了很多知识尤其对vue组件指令、渲染数据、计算属性、绑定事件以及vuex状态管理、vue-router路由的使用有了一定了解.

如果你想学微信小程序可以来看看我写的一个商城项目 [地址点这里](https://github.com/Mynameisfwk/wechat-app-vivo)<br>
刚出来的快应用，可以看看我写的商城项目 [地址点这里](https://github.com/Mynameisfwk/shop-quickapp)<br>
这个项目后期会重构，~~到时候会加上MongoDB+node.js来写后台~~，欢迎大家踊跃issues [提出建议](https://github.com/Mynameisfwk/vivo-shop/issues)<br>
另外如果这个项目对你有帮助欢迎各位fork、star，谢谢

## 项目正在重构中会加上很多功能！有想知道进展的———>>>>  qq2239657654
## 正在重构需要加什么功能请移步issues给出建议[点这里> issues](https://github.com/Mynameisfwk/vivo-shop/issues)
## 前端交流群 740625675

# 技术栈
> [vue.js](https://cn.vuejs.org/) 构建用户界面的 MVVM 框架，核心思想是：数据驱动、组件系统。

> [vue-cli](https://www.npmjs.com/package/vue-cli) 是vue的脚手架工具，目录结构、本地调试、代码部署、热加载、单元测试。

> [vue-router](https://router.vuejs.org/zh-cn/) 是官方提供的路由器，使用vue.js构建单页面应用程序变得轻而易举。

> [vue-resource](https://www.npmjs.com/package/vue-resource) 请求数据，服务器通讯，官方推荐[axios](https://www.npmjs.com/package/axios)请求数据，本项目后期改用[axios](https://www.npmjs.com/package/axios)。

> [vuex](https://vuex.vuejs.org/zh-cn/) 是一个专为 vue.js 应用程序开发的状态管理模式，简单来说Vuex就是管理数据的。

> [Mint UI](http://mint-ui.github.io/#!/zh-cn) 由饿了么前端团队推出的 Mint UI 是一个基于 vue.js 的移动端组件库。


# 项目预览
:point_right: [在线预览](http://fwk01.top/#/ "链接已失效,请自行下载预览")<br>
建议在手机或F12手机模式下浏览

# 基本功能
* 首页轮播图效果
* 首页轮播图实现链接跳转
* 商品详情列表
* 商品详情轮播效果
* 商品立即购买/加入购物车
* 购物车加减数量删除商品
* 购物车结算清空
* 商品删除结算
* 模拟支付成功后加入订单
* 商品订单删除
* 模拟订单付款成功生成订单
* Localstorage本地存储数据


# 效果截图演示

![](https://github.com/Mynameisfwk/vivo-shop/blob/master/static/lowSource/1.0.gif)
![](https://github.com/Mynameisfwk/vivo-shop/blob/master/static/lowSource/2.0.png)
![](https://github.com/Mynameisfwk/vivo-shop/blob/master/static/lowSource/3.0.png)
![](https://github.com/Mynameisfwk/vivo-shop/blob/master/static/lowSource/4.0.png)

# 重构之后的效果展示
## 加了很多东西还有功能 先放上来看看吧，还有一点没写完 写完就传上来

#### 首页、分类、动态、我的
![](https://user-gold-cdn.xitu.io/2018/5/17/1636ba09e546a28f?w=1555&h=608&f=png&s=419439)

#### 商品详情、提交订单、订单详情
![](https://user-gold-cdn.xitu.io/2018/5/17/1636bb0a57c224a2?w=1555&h=608&f=png&s=305775)

#### 我的订单、我的收藏、我的购物车
![](https://user-gold-cdn.xitu.io/2018/5/18/16371fa7f4ceb8e2?w=1555&h=608&f=png&s=247923)



## 项目安装及运行

``` bash
# 安装项目依赖
npm install 

# 启动服务 浏览器本地访问http://localhost:8080
npm run dev

# 编译打包
npm run build

```




