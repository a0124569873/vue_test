from module.BOSS import *
from dao.BaseDao import *

class BOSSDao(BaseDao):

    def addRecord(self, record):

        if type(record) == list:
            self.session.add_all(record)
        else:
            self.session.add(record)
            self.session.commit()

    def getRecord(self):

        # ret = session.query(BOSS).all()

        # ret = session.query(Users.name, Users.extra).all()

        ret = self.query(BOSS).filter_by(job_name='alex1').all()

        # ret = session.query(Users).filter_by(name='alex').first()

        return ret


# obj = BOSS(job_name='alex1', jod_desc='sd', company='aafgdfga', company_desc='xxx')
#
# obj_list = [
#     BOSS(job_name='alex1', jod_desc='sd', company='aaa', company_desc='xxx'),
#     BOSS(job_name='alex1', jod_desc='sd', company='aaa', company_desc='xxx')
# ]
#
# try:
#     # addRecord(obj)
#     # print(getRecord()[0].getjson())
#
#     for aa in getRecord():
#         print(aa.getjson())
#
#
# except Exception as e:
#     init_db()