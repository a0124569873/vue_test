from requests_html import HTMLSession
import time
from module.BOSS import *
from dao.BOSSDao import *

class BOSSService():

    host = "https://www.zhipin.com"

    def getAllUrl(self, url):
        session = HTMLSession()
        r = session.get(url)
        job_list_div = r.html.find(".job-list", first=True)
        job_list = job_list_div.find(".info-primary")
        res_list = []
        for list_item in job_list:
            res_list.append(list_item.find('a', first=True).attrs["href"])
        return res_list

    def getPageRangeLink(self, maxpage = 10):
        res_each_job_list = []
        for eachpage in range(maxpage):
            url = "https://www.zhipin.com/c101010100/h_101010100/?query=python&page=" + str(eachpage) + "&ka=page-" + str(eachpage)
            res_each_job_list = self.getAllUrl(url)
            print(res_each_job_list)
            for each_link in res_each_job_list:
                fin_url = self.host + each_link
                res = self.getEachPageInfo(fin_url)
                bossDao = BOSSDao()
                if type(res) == str:
                    continue
                bossDao.addRecord(res)


    def getEachPageInfo(self, url):
        session = HTMLSession()
        r = session.get(url)
        try:
            job_name_div = r.html.find(".info-primary", first=True)
            job_name = job_name_div.find(".name", first=True).find('h1',first=True).text
            # print(job_name)
            job_sec_div = r.html.find(".job-sec", first=True)
            job_sec = job_sec_div.find(".text", first=True).text
            # print(job_sec.strip())
            company_div = r.html.find(".info-company", first=True)
            company_name = company_div.find("h3", first=True).text
            # print(company_name)
            company_sec_div = r.html.find(".company-info", first=True)
            company_sec = company_sec_div.find(".text", first=True).text
            # print(company_sec)
            time.sleep(2)
            return BOSS(job_name=job_name, jod_desc=job_sec, company=company_name, company_desc=company_sec)
        except Exception as e:
            print(e)
            return "failed"

bossService = BOSSService()
bossService.getPageRangeLink()
