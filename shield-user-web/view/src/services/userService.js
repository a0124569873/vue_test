import xhr from './xhr/'

class UserService {
  getUserInfo() {
    return xhr({
      url: 'users/profile',
      method: 'get',
      body: {}
    })
  }

  sendPhone(phone) {
    return xhr({
      url: 'safephone/send',
      method: 'post',
      body: {
        safephone: phone
      }
    })
  }

  sendEmail(email) {
    return xhr({
      url: 'safemail/send',
      method: 'post',
      body: {
        safemail: email
      }
    })
  }

  updatePhone(senddata) {
    return xhr({
      url: 'safephone',
      method: 'PUT',
      body: {
        safephone: senddata.safephone,
        token: senddata.token
      }
    })
  }

  updateEmail(senddata) {
    return xhr({
      url: 'safemail',
      method: 'PUT',
      body: {
        safemail: senddata.safemail,
        token: senddata.token
      }
    })
  }

  updateRealName(senddata) {
    return xhr({
      url: 'realname',
      method: 'PUT',
      body: {
        real_name: senddata.real_name,
        id_number: senddata.id_number,
        captcha: senddata.captcha
      }
    })
  }

  getOrderList(senddata) {
    return xhr({
      url: 'order',
      method: 'get',
      body: senddata
    })
  }

  getOrderInfo(fee) {
    return xhr({
      url: 'order',
      method: 'post',
      body: {
        type: 1,
        fee: fee,
        detail: {
          product_id: 0
        }
      }
    })
  }

  delList(id) {
    return xhr({
      url: `order/${id}`,
      method: 'delete',
      body: {}
    })
  }
}
export default new UserService()
