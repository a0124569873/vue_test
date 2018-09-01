from requests_html import HTMLSession
import time
from module.BOSS import *
from dao.BOSSDao import *

class ZhiLianService():

    host = "https://www.zhipin.com"

    def getAllUrl(self, url):
        session = HTMLSession()
        r = session.get(url)
        job_list_div = r.html.find(".newlist")
        res_list = []
        for job_list_div_item in job_list_div:
            job_list_div_item_tr = job_list_div_item.find('.zwmc', first=True).find('a', first=True)
            if str(job_list_div_item_tr) == "None":
                continue
            res_list.append(job_list_div_item_tr.attrs['href'])
        return res_list

    def getPageRangeLink(self, maxpage = 10):
        res_each_job_list = []
        for eachpage in range(maxpage):
            url = "https://sou.zhaopin.com/jobs/searchresult.ashx?bj=160000&sj=044%3B045%3B079&in=160400&jl=%E5%8C%97%E4%BA%AC&kw=python&p=" + str(eachpage) + "&isadv=0"
            res_each_job_list = self.getAllUrl(url)
            for each_link in res_each_job_list:
                res = self.getEachPageInfo(each_link)
                print(res)
                bossDao = BOSSDao()
                if type(res) == str:
                    continue
                bossDao.addRecord(res)

    def getEachPageInfo(self, url):
        session = HTMLSession()
        r = session.get(url)
        try:
            job_name_div = r.html.find(".inner-left", first=True)
            job_name = job_name_div.find('h1',first=True).text
            # print(job_name)
            com_job_sec_div = r.html.find(".tab-cont-box", first=True)
            com_job_sec_divs = com_job_sec_div.find(".tab-inner-cont")
            res = []
            for eachitem in com_job_sec_divs:
                ps = eachitem.find('p')
                p_content = ''
                for eachp in ps:
                    p_content += eachp.text
                res.append(p_content)
            job_sec = res[0]
            company_sec = res[1]
            company_div = r.html.find(".inner-left", first=True)
            company_name = company_div.find("h2", first=True).text
            # print(company_name)
            time.sleep(2)
            return BOSS(job_name=job_name, jod_desc=job_sec, company=company_name, company_desc=company_sec)
        except Exception as e:
            print(e)
            return "failed"

bossService = ZhiLianService()
bossService.getPageRangeLink()
# res = bossService.getEachPageInfo("http://jobs.zhaopin.com/CC152362529J00038850912.htm").getlist()
# print(res)