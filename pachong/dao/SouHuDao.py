from module.SouHu import *
from dao.BaseDao import *
class SouHuDao(BaseDao):
    def addRecord(self, record):
        try:
            if type(record) == list:
                self.session.add_all(record)
            else:
                self.session.add(record)
            self.session.commit()
        except Exception as e:
            # self.init_db()
            # self.addRecord(record)
            print(e)
            pass

    def getRecord(self):
        ret = self.session.query(SouHu).all()
        return ret

    def getTotalRecord(self):
        ret = self.session.query(SouHu).count()
        return ret
    def getPageRecord(self,pagesize = 20, pageindex = 1):
        ret = self.session.query(SouHu).limit(pagesize).offset((pageindex - 1) * pagesize).all()
        return ret