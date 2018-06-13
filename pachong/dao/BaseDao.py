from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from sqlalchemy.ext.declarative import declarative_base

Base = declarative_base()

class BaseDao:
    engine = create_engine("mysql+pymysql://root:root@127.0.0.1:3306/zxn?charset=utf8", max_overflow=5)
    Session = sessionmaker(bind=engine)  # 指定引擎
    session = Session()

    def init_db(self,base):
        # 创建表
        base.metadata.create_all(self.engine)