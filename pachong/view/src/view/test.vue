<template>
    <div class="wrap">
        <el-row>
          <el-col :span="24">
              <el-table 
              stripe
              border
              :data="pagedata"
              >
                <el-table-column label="标题">
                    <template slot-scope="scope">
                        {{ scope.row.news_name }}
                    </template>
                </el-table-column>
                <el-table-column label="时间">
                    <template slot-scope="scope">
                        {{ scope.row.news_time }}
                    </template>
                </el-table-column>
                <el-table-column label="详情" width="180">
                    <template slot-scope="scope" style="width:500px">
                        <el-popover trigger="hover" placement="top">
                        <p>内容: {{ scope.row.news_desc }}</p>
                        <div slot="reference">
                            查看详情
                        </div>
                        </el-popover>
                    </template>
                </el-table-column>
              </el-table>
          </el-col>
        </el-row>
        <div class="gstatus-page" style="text-align: center">
			<el-pagination
				@size-change="handleSizeChange"
				@current-change="handleCurrentChange"
				:current-page="currentPage"
				:page-sizes="[5, 10, 20, 50]"
				:page-size="pagesize"
				layout="total, sizes, prev, pager, next, jumper"
				:total="totalitem">
			</el-pagination>
		</div>
    </div>
</template>
<script>

import axios from 'axios'
let Base64 = require('js-base64').Base64

export default {
    name: 'testpage',
    mounted(){
        this.modifypage()
    },
    data(){
        return {
            pagedata: [],
            totalitem: 10,
            currentPage: 1,
            pagesize: 20
        }
    },
    methods:{
        _loaddata(){
            axios.get("/").then((res) => {
                console.log(res)
            })
        },
        handleSizeChange (val) {
            this.pagesize = val
            this.modifypage()
        },
        handleCurrentChange (val) {
            this.currentPage = val
            this.modifypage()
        },
        modifypage(){
            axios.get(`/getSouHuPageRecord?pagesize=${this.pagesize}&pageindex=${this.currentPage}`).then((res) => {
                let res_json = res.data
                console.log(res.data)
                if (res_json.errcode === 0) {
                    this.totalitem = res_json.total
                    this.pagedata = res_json.res_json.map((item) => {
                        item.news_desc = Base64.decode(item.news_desc)
                        // console.log(this.utf8Decode(item.news_desc))
                        return item
                    })
                }
                // alert(res)
            })
        }
    }
}
</script>

