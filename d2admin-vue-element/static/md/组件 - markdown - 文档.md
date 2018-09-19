## 参数

| 参数名 | 介绍 | 必选 | 值类型 | 可选值 | 默认值 |
| --- | --- | --- | --- | --- | --- |
| url | markdown文件地址 | 非 | String |  | 无 |
| md | markdown内容 | 非 | String |  | 无 |
| highlight | 代码高亮 | 非 | Boolean |  | false |
| baidupan | 百度网盘分享链接显示优化 | 非 | Boolean |  | true |

## 使用方法

加载一个.md文件

```
<markdown url="/static/md/xxxx.md"></markdown>
```

加载资源

```
const md = `# Header

## title

text`

<markdown :md="md"></markdown>
```

## 百度网盘分享链接优化

当书写类似下面的分享链接时

> 需要 `baidupan = true`

```
普通分享链接

> https://pan.baidu.com/s/1kW6uUwB

私密分享链接

> 链接: https://pan.baidu.com/s/1ggFW21l 密码: 877y
```

markdown 中引用部分的文本由于被识别为百度云的分享链接，所以不会被当做 `blockquote` 渲染（非百度云链接的引用行不会改变），会以一种特别的块来显示

就像这样

> https://pan.baidu.com/s/1kW6uUwB

上面的块会嵌套在你的 markdown 渲染结果中

> 了解 D2Admin 是如何在 markdown 中匹配百度云链接的？ [查看源码](https://github.com/FairyEver/d2admin-vue-element/blob/master/src/components/core/Markdown/plugin/baidupan.js)