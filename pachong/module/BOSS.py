#!/usr/bin/env python
# -*- coding:utf-8 -*-
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, String, ForeignKey, UniqueConstraint, Index, Text

Base = declarative_base()

# 创建单表
class BOSS(Base):
    __tablename__ = 'boss'
    id = Column(Integer, primary_key=True)
    job_name = Column(String(250))
    jod_desc = Column(Text)
    company = Column(String(50))
    company_desc = Column(Text)
    __table_args__ = (
        UniqueConstraint('id', 'job_name', name='uix_id_name'),
        Index('ix_id_name', 'job_name','jod_desc','company','company_desc' ),
    )

    def getlist(self):

        res_json = {
            'job_name': self.job_name,
            'jod_desc': self.jod_desc,
            'company': self.company,
            'company_desc': self.company_desc,
        }
        return res_json