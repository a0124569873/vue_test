import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const state = {
  status: false,
  login: false,
  cert: {
    status: 'missing'
    // status: 'valid'
  },
  hostshold: 0
}
const mutations = {

  EDIT_NOTE (state, text) {
    state.login = text
  },
  EDIT_STATUS (state, text) {
    state.status = text
  },
  EDIT_HOSTSHOLD (state, text) {
    state.hostshold = text
  }
}
const store = new Vuex.Store({
  state,
  mutations
})

export default store
