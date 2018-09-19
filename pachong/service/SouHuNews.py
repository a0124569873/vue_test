from requests_html import HTMLSession
from module.SouHu import *

import base64

session = HTMLSession()


from dao.SouHuDao import *


class SouHuNews():
    
    def getEachPageInfo(self, url):
        r = session.get(url)
        try:
            news_name_div = r.html.find(".text-title", first=True)
            news_name = news_name_div.find('h1', first=True).text
            # print(news_name)
            news_sec_div = r.html.find("article", first=True)
            news_sec = news_sec_div.text
            # print(news_sec)
            time_div = r.html.find("#news-time", first=True)
            news_time = time_div.text
            # vvv = base64.b64encode(news_sec.encode('utf-8'))
            return SouHu(news_name=news_name, news_time=news_time, news_desc=base64.b64encode(news_sec.encode('utf-8')))

        except Exception as e:
            print(e)

    def getAllUrl(self, url):
        r = session.get(url)
        try:
            res_url_list = []
            url_list = r.html.links
            for each_url in url_list:
                if "www.sohu.com/a/" in each_url:
                    if 'http' not in each_url:
                        each_url = 'http:' + each_url
                    res_url_list.append(each_url)
            return res_url_list
        except Exception as e:
            print(e)

    def getRes(self):
        url_list = self.getAllUrl("http://www.sohu.com/")
        res_list = []
        for each_url in url_list:
            res_list.append(self.getEachPageInfo(each_url))

        s = SouHuDao()
        s.addRecord(res_list)



# ccc =  SouHuNews().getRes()

# print(ccc.getlist())
#
# s = SouHuDao()
# res = s.getRecord()
# for each_res in res:
#     each_res_json = each_res.getlist()
#     each_res_json["news_desc"] = base64.b64decode(each_res_json["news_desc"]).decode("utf8")
#     print(each_res_json)
# s.init_db(SouHu)