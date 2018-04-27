import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex)

const state = {
    zzz:10,
    login: false
}

const mutations = {
    add_zzz(state){
        state.zzz = state.zzz + 1
    }
}

const store = new Vuex.Store({
    state,
    mutations
})

export default store