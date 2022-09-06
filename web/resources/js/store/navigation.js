const state = {
    navigation: true,
};

const getters = {
    navigation: state => state.navigation,
};

const mutations = {
    changeNavState (state) {
        state.navigation = !state.navigation;
    }
}

const actions = {
    changeNavState (context) {
        context.commit('changeNavState');
    }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};