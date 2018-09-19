// https://eslint.org/docs/user-guide/configuring

module.exports = {
  root: true,
  parser: 'babel-eslint',
  parserOptions: {
    ecmaVersion: 2017,
    sourceType: 'module'
  },
  env: {
    browser: true,
    node: true,
    es6: true,
    commonjs: true
  },
  extends: ['standard'],
  // required to lint *.vue files
  plugins: ['vue'],
  // add your custom rules here
  rules: {
    'space-before-function-paren': [0, 'always'],
    'comma-dangle': ['error', 'ignore'],
    'no-trailing-spaces': 0,
    'no-multi-str': 0,
    'keyword-spacing': 0,
    'spaced-comment': [
      'error',
      'always',
      {
        line: {
          markers: ['/'],
          exceptions: ['-', '+']
        },
        block: {
          markers: ['!'],
          exceptions: ['*'],
          balanced: true
        }
      }
    ],
    indent: ['error', 2],
    camelcase: 0,
    'no-new': 0,
    // allow paren-less arrow functions
    'arrow-parens': 0,
    'no-useless-escape': 0,
    // allow async-await
    'generator-star-spacing': 0,
    // allow debugger during development
    'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0
  },
  globals: {
    $: true,
    _DEV_: true,
    JSEncrypt: true,
    G2: true
  }
}
