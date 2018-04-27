import xhr from './xhr'
class netaddrService{
  getNetaddr(){
    return xhr({
      url: 'system/net?oper=get',
      method: 'GET'
    })
  }

  setNetaddr(net){
    return xhr({
      url:'system/net?oper=set',
      method:'POST',
      contentType:'JSON',
      body:{
        'net':net
      }
    })
  }
}
export default new netaddrService()