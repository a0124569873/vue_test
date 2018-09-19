import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const state = {
  access: false,
  cert: false,
  web_ui: false,
  // login: false,
  login: {is_login: false, uemail: ''},
  time: '',
  lines: [],
  site_info: {}
}
const mutations = {
  EDIT_NOTE (state, text) {
    state.login = text
  },
  EDIT_WEB (state, text) {
    state.web_ui = text
  },
  EDIT_Access (state, text) {
    state.access = text
  },
  EDIT_Time (state, text) {
    state.time = text
  },
  EDIT_SITE (state, text) {
    state.site_info = text
  },
  INIT_LINES(state, payload) {
    state.lines = payload
  }
}
const store = new Vuex.Store({
  state,
  mutations,
  getters: {
    linesMap({ lines }) {
      const map = {}
      for(let { text, value } of lines) {
        map[value] = { text, value }
      }
      return map
    }
  }
})

export default store
