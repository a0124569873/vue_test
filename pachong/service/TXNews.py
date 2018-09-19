from requests_html import HTMLSession
import time
data = time.strftime("%Y-%m-%d")
timestamp = str(int(round(time.time() * 1000 )))

type_list = ["news", "ent", "sports", "finance", "games", "auto", "fashion", "edu", "house"]

url = "http://roll.news.qq.com/interface/cpcroll.php?callback=rollback&site=" + "news" + "&mode=1&cata=&date=" + data +  "&page=1&_=" + timestamp


def getNewsList(url):
    session = HTMLSession()
    r = session.get(url)
    print(r.text)
getNewsList(url)