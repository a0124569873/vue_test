from flask import Flask, request
import json, base64
from dao.SouHuDao import *
app=Flask(__name__)
souHuDao = SouHuDao()

@app.route("/getSouHuCount")
def getSouHuCount():
    return str(souHuDao.getTotalRecord())
    # return "sdfsdfsdf"

@app.route("/getSouHuPageRecord")
def getSouHuPageRecord():
    pageindex = int(request.args.get("pageindex"))
    pagesize = int(request.args.get("pagesize"))
    res_list = souHuDao.getPageRecord(pagesize=pagesize, pageindex=pageindex)
    totalRecord = souHuDao.getTotalRecord()
    res_arr = []
    for res in res_list:
        each_res_json = res.getlist()
        # each_res_json["news_desc"] = base64.b64decode(each_res_json["news_desc"]).decode("utf8")
        res_arr.append(each_res_json)
    # res_json = json.dumps(res_arr)
    return json.dumps({
        'errcode':0,
        "res_json": res_arr,
        "total": totalRecord
    })

if __name__ == '__main__':
    app.run()