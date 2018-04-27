import xhr from './xhr/'

/**
 * 对应后端涉及到用户认证的 API
 */
class SysSerivce {
    addWanLan (params) {
        return xhr({
            url: 'setting/interface/set',
            method: 'POST',
            body: params
        })
    }

    getWanLan () {
        return xhr({
            url: 'setting/interface/get',
            method: 'GET'
        })
    }

    getlist (sentdata) {
        return xhr({
            url: 'setting/get?type=net&row=15&page='+sentdata.page
        })
    }
   setlist (sentdata) {
        return xhr({
            url: 'setting/'+ sentdata.oper,
            method: 'POST',
			body:{
                type:sentdata.type,
                page:sentdata.page,
                row:15,
                vpn_source_ip:sentdata.vpn_source_ip,
                mask_ip:sentdata.mask_ip,
                ids:sentdata.ids
            }
        })
    }

    getHostListt (sentdata) {
        return xhr({
            url: 'setting/get?type=fep&row=15&page='+sentdata.page
        })
    }
    hostList (sentdata) {
        return xhr({
            url: 'setting/'+ sentdata.oper,
            method: 'POST',
			body:{
                type: sentdata.type,
                ids: sentdata.ids,
                fep_ip: sentdata.fep_ip,
                fep_vxlan: sentdata.fep_vxlan,
                g_mac: sentdata.g_mac,
                g_vxlan: sentdata.g_vxlan,
                g_type: sentdata.g_type,
                group_d_ip: sentdata.group_d_ip,
                group_d_mac: sentdata.group_d_mac
            }
        })
    }
    getVpn(sentdata){
        return xhr({
            url: 'setting/get?type=vpn&row=15&page='+sentdata.page
        })
    }
    setVpn(sentdata){
        return xhr({
            url: 'setting/'+ sentdata.oper,
            method: 'POST',
			body:{
                type: sentdata.type,
                account: sentdata.account,
                command: sentdata.command,
                ids: sentdata.ids,
                ip: sentdata.ip
            }
        }) 
    }
    
    getTrans(sentdata){
        return xhr({
            url: 'setting/trans/get?row=15&page='+sentdata.page
        })
    }
    setTrans(sentdata){
        return xhr({
            url: 'setting/trans/'+ sentdata.oper,
            method: 'POST',
			body:{
                ids: sentdata.ids,
                conf: sentdata.config
            }
        }) 
    }
}

// 实例化后再导出
export default new SysSerivce()