import xhr from './xhr/'
class TopoService {
  getTopo () {
    return xhr({
      url: 'stats/dev?t=6&topo=true',
      method: 'get'
    })
  }

  getTopoStats () {
    return xhr({
      url: 'stats/dev?t=6',
      method: 'get'
    })
  }
}

export default new TopoService()
