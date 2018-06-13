#!/usr/bin/env python
# -*- coding:utf-8 -*-
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, String, ForeignKey, UniqueConstraint, Index, Text

Base = declarative_base()

# 创建单表
class SouHu(Base):
    __tablename__ = 'souhu'
    id = Column(Integer, primary_key=True)
    news_name = Column(String(250))
    news_desc = Column(Text(10000))
    news_time = Column(String(50))
    __table_args__ = (
        UniqueConstraint('id', 'news_name', name='uix_id_name'),
        Index('ix_id_name', 'news_name','news_desc','news_time'),
    )

    def getlist(self):

        res_json = {
            'news_name': self.news_name,
            'news_desc': self.news_desc,
            'news_time': self.news_time,
        }
        return res_json