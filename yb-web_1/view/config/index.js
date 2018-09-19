// see http://vuejs-templates.github.io/webpack for documentation.
var path = require('path')

module.exports = {
  build: {
    env: require('./prod.env'),
    index: path.resolve(__dirname, '../../api/application/index/view/index/index.html'),
    assetsRoot: path.resolve(__dirname, '../../api/public/'),
    assetsSubDirectory: 'static',
    assetsPublicPath: '/',
    productionSourceMap: false,
    // Gzip off by default as many popular static hosts such as
    // Surge or Netlify already gzip all static assets for you.
    // Before setting to `true`, make sure to:
    // npm install --save-dev compression-webpack-plugin
    productionGzip: false,
    productionGzipExtensions: ['js', 'css'],
    // Run the build command with an extra argument to
    // View the bundle analyzer report after build finishes:
    // `npm run build --report`
    // Set to `true` or `false` to always turn it on or off
    bundleAnalyzerReport: process.env.npm_config_report
  },
  dev: {
    env: require('./dev.env'),
    port: 8083,
    autoOpenBrowser: true,
    assetsSubDirectory: 'static',
    assetsPublicPath: '/',
    proxyTable: {
      '/setting': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/user/*': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/stats': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/logs': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/system': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/guide': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/captcha.html': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/admin': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/uconnect': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/maskpool': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/vpnmap': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      },
      '/relink': {
        target: 'https://192.168.2.18',
        secure: false,
        changeOrigin: true
      }
    },
    // CSS Sourcemaps off by default because relative paths are "buggy"
    // with this option, according to the CSS-Loader README
    // (https://github.com/webpack/css-loader#sourcemaps)
    // In our experience, they generally work as expected,
    // just be aware of this issue when enabling this option.
    cssSourceMap: false
  }
}
