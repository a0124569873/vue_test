import xhr from './xhr/'
class getXhrData {
    getChartData(){
        return xhr({
            url:'/index.php'
        })
    }

    gettopoData(){
        return xhr({
            url:'/stats/dev?t=6&topo=true'
        })
    }

    getGstatus(){
        return xhr({
            url:'/stats/dev?t=6'
        })
    }
}

export default new getXhrData();