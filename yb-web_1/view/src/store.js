const store = {
  login: window.sessionStorage.getItem('login') === 'true' || false,
  status: 0
}
export default store
