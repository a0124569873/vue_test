import xhr from './xhr/'

class CurrentService {
    getState (type) {
        return xhr({
            url: 'stats/get?t=2|3|4&r=' + type,
            method: 'get'
        })
    }
    getFlow (type, IP = '') {
        if (IP === '' || IP === 'all') {
            return xhr({
                url: 'stats/get?t=1&r=' + type,
                method: 'get'
            })
        }
        return xhr({
            url: 'stats/get?t=1&r=' + type + '&ip=' + IP,
            method: 'get'
        })
    }
    getSysData (t) {
        return xhr({
            url: 'stats/get?t=' + t,
            method: 'get'
        })
    }
    getNetStatus () {
        return xhr({
            url: 'stats/gett=17',
            method: 'get'
        })
    }
    getHostStatus (orderby = 'in_bps', limit = 10, desc = true) {
        return xhr({
            url: 'stats/get?t=18&orderby=' + orderby + '&limit=' + limit + '&desc=' + desc,
            method: 'get'    
        })
    }
    getTmpBlackWhiteList ({page = 1, row = 20, filterIp = '', type = '1'} = {}) {
        return xhr({
            url: `stats/get?t=19&list_type=${type}`,
            method: 'get'
        })
    }
    delTmpBlackWhiteList ({params = {}, type = '1'} = {}) {
        return ({
            url: `stats/del?t=19&list_type=${type}`,
            method: 'post',
            body: params
        })
    }
}

export default new CurrentService()