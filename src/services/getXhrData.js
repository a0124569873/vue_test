import xhr from './xhr/'
class getXhrData {
    getChartData(){
        return xhr({
            url:'/index.php'
        })
    }
}

export default new getXhrData();