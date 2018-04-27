import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const state = {
  login: false,
  hostshold: 0
}
const mutations = {

  EDIT_NOTE (state, text) {
    state.login = text
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
