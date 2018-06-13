<template>
  <div class="high-ip">
    <secondBar title="高防IP"></secondBar>
    <el-card v-loading="loading">
      <div class="filters mb-lg" style="overflow:hidden">
      <label class="lbl-text">地&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;域：</label>
      <div style="padding-left:80px;">
        <div style="word-spacing: -4px; margin-bottom: 3px">
          <xRadio name="area"
                  v-model="areaVal"
                  class="mb-sm"
                  :label="-1"
                  :key="-1">全部</xRadio>
          <xRadio v-for="item in areas"
                  class="mb-sm"
                  name="area"
                  v-model="areaVal"
                  :label="item.value"
                  :key="item.value">{{item.label}}</xRadio>
        </div>
        <div style="word-spacing: -4px" v-if="citys">
          <xRadio name="city"
                  v-model="cityVal"
                  class="mb-sm"
                  :label="-1"
                  :key="-1">全部</xRadio>
          <xRadio v-for="item in citys"
                  name="city"
                  v-model="cityVal"
                  class="mb-sm"
                  :label="item.value"
                  :key="item.value">{{item.label}}</xRadio>
        </div>
      </div>
    </div>

    <div class="filters mb-lg" style="overflow:hidden">
      <label class="lbl-text mr-md">实例类型：</label>
      <div>
        <el-select v-model="type" class="mr-lg mb-sm vt" placeholder="请选择" size="mini" style="width:120px">
          <el-option
            v-for="item in typeOptions"
            :key="item.value"
            :value="item.value"
            :label="item.label">
          </el-option>
        </el-select>

        <el-input size="mini"
          class="high-ip-search mr-lg mb-sm vt"
          style="width:230px"
          v-model.trim="subTypeVal"
         >
          <el-select slot="prepend"
            v-model="subType"
            placeholder="请选择"
            style="width:90px"
            >
            <el-option value="id" label="实例ID"></el-option>
            <el-option value="ip" label="IP"></el-option>
          </el-select>
        </el-input>

        <div style="display:inline-block;overflow:hidden">
          <label class="lbl-text mb-sm mr-md">实例状态：</label>
          <el-select v-model="status" class="mr-lg mb-sm" placeholder="请选择" size="mini" style="width:90px">
            <el-option
              v-for="item in statusOptions"
              :key="item.value"
              :value="item.value"
              :label="item.label">
            </el-option>
          </el-select>
        </div>

        <div style="display:inline-block;overflow:hidden">
          <label class="lbl-text mb-sm mr-md">线路类型：</label>
          <el-select v-model="lineType" class="mr-lg mb-sm" placeholder="请选择" size="mini" style="width:150px">
            <el-option
              v-for="item in lineTypeOptions"
              :key="item.value"
              :value="item.value"
              :label="item.label">
            </el-option>
          </el-select>
        </div>

        <el-button type="primary" size="mini" class="vt" @click="fetch" icon="el-icon-search">查询</el-button>
      </div>
    </div>
      <el-table :data="tableData" style="background-color:#f9f9f9;">
        <el-table-column label="实例信息" prop="id">
          <template slot-scope="{ row }">
            <ul>
              <li>ID: {{ row.instance_id }}</li>
              <li>{{ getInstanceType(row.type) }}</li>
              <li>到期时间：{{ row.end_date | formattime(true) }}</li>
              <li>业务宽带：{{ row.normal_bandwidth }}Gb </li>
              <li style="color:#409eff;cursor:pointer;" v-if="!row.status" @click="showEnable(row)">立即启用</li>
            </ul>
          </template>
        </el-table-column>
        <el-table-column label="线路信息">
          <template slot-scope="{ row }">
            <ul>
              <li>{{ getLineType(row.instance_line) }}</li>
              <li v-if="mapAreas[row.area]">地区：{{ mapAreas[row.area]}}</li>
              <li v-for="line in row.hd_ip" :key="line.ip">
                - {{ getLineType(line.line) }}&nbsp;&nbsp;{{ line.ip }}
              </li>
            </ul>
          </template>
        </el-table-column>
        <el-table-column label="防护信息">
          <template slot-scope="{ row }" >
            <ul>
              <li>防护域名数：{{ getPortSiteCount(row.hd_ip) || '-'}} （最多{{ row.port_count != null ? row.port_count : row.site_count }}个）</li>
              <li>防护带宽：{{ row.base_bandwidth || 0.00 }}Gb（弹性{{ row.bandwidth || 0.00 }}Gb）</li>
            </ul>
          </template>
        </el-table-column>

        <el-table-column label="安全统计" prop="type">
          <template slot-scope="{ row }">
            <ul>
              <li>DDoS攻击峰值：0.00G</li>
              <li>DDoS攻击次数：-</li>
              <li style="color:#409eff;cursor:pointer;">
                <router-link :to="`/high/${row.id}/report/banWidth`" @click.native="$event.stopPropagation()">查看报表</router-link>
              </li>
            </ul>
          </template>
        </el-table-column>
      </el-table>
      <div class="f-x f-je mt-lg">
        <el-pagination background
          @current-change="fetch"
          :current-page.sync="curPage"
          :page-size="pageSize"
          layout="total, prev, pager, next"
          :total="total"></el-pagination>
      </div>

    </el-card>
    <!-- 立即启用弹框 -->
    <vd-dialog
      title="启用实例"
      :visible.sync="visible"
      width="600px"
      cancelText="取消"
      confirmText="立即启用"
      :confirmDisabled="confirmDisabled"
      @onCancel="onCancel"
      @onConfirm="onConfirm">
      <el-form label-width="100px" v-loading="dialogLoading">
        <el-alert
          type="warning"
          title="建议选择离您的业务更近的线路地域，一旦选择不可更改。"
          show-icon
          :closable="false">
        </el-alert>
        <el-form-item label="选择线路：" class="mt-lg">
          <span v-if="areaVal2 == '-1'">暂无可选区域</span>
          <div style="word-spacing:-4px;margin-bottom:3px;line-height:1">
            <xRadio v-for="item in areas2"
                    class="mb-sm"
                    name="area"
                    v-model="areaVal2"
                    :label="item.value"
                    :key="item.value">{{item.label}}</xRadio>
          </div>
          <div style="word-spacing:-4px;line-height:1" v-if="citys2">
            <xRadio v-for="item in citys2"
                    name="city"
                    v-model="cityVal2"
                    class="mb-sm"
                    :label="item.value"
                    :key="item.value">{{item.label}}</xRadio>
          </div>
        </el-form-item>
      </el-form>
    </vd-dialog>
  </div>
</template>
<script>
import secondBar from 'components/secondBar'
import xRadio from 'components/xRadio'
import vdDialog from 'components/vdDialog'
import areas, { mapAreas } from 'utils/areas'
import ddos from 'services/ddosService'

export default {
  components: {
    secondBar,
    xRadio,
    vdDialog
  },
  data() {
    return {
      typeOptions: [ // 实例类型
        {
          value: '-1',
          label: '全部'
        },
        {
          value: '1,2',
          label: '网站类'
        },
        {
          value: '3',
          label: '应用类'
        },
        {
          value: '1',
          label: '网站类-共享'
        },
        {
          value: '2',
          label: '网站类-独享'
        }
      ],
      statusOptions: [
        {
          value: '-1',
          label: '全部'
        },
        {
          value: '0',
          label: '未启用'
        },
        {
          value: '1',
          label: '已启用'
        }
      ],
      lineTypeOptions: [
        {
          value: '-1',
          label: '全部'
        },
        {
          value: '0',
          label: '海外'
        },
        {
          value: '1',
          label: '电信'
        },
        {
          value: '2',
          label: '联通'
        },
        {
          value: '3',
          label: '电信、联通'
        },
        {
          value: '4',
          label: '移动'
        },
        {
          value: '5',
          label: '电信、移动'
        },
        {
          value: '6',
          label: '移动、联通'
        },
        {
          value: '7',
          label: '电信、联通和移动'
        },
        {
          value: '8',
          label: 'BGP'
        },
        {
          value: '11',
          label: '电信、联通和BGP'
        },
      ],
      showDialog: true,
      loading: false,
      areas,
      mapAreas: mapAreas,
      areaVal: -1,
      cityVal: -1,
      areas2: [], // 立即启用弹框中地区选择
      areaVal2: -1,
      type: '-1', // 实例类型
      subType: 'id', // 实例ID，IP
      subTypeVal: '', // input value, 实例ID | IP
      ip: '', // IP
      id: '', // 实例ID
      status: '-1', // 实例状态
      lineType: '-1', // 线路类型
      tableData: [],
      visible: false,
      editId: '',   // 当前修改实例ID
      lineAreas: [], // 线路对应的可选地区
      curPage: 1,
      pageSize: 5,
      total: 0,
      confirmDisabled: false,
      dialogLoading: true,
    }
  },
  computed: {
    citys() {
      const target = areas.find(item => item.value === this.areaVal)
      return target ? target.children : null
    },

    citys2() {
      const target = this.areas2.find(item => item.value === this.areaVal2)
      return target ? target.children : null
    },

    enableArea() {
      return this.cityVal2 != -1 ? this.cityVal2
                : this.areaVal2 != -1 ? this.areaVal2 : ''
    },

    query() {
      const params = {
        _from: (this.curPage - 1) * 5,
        _size: this.pageSize
      }
      if (this.type != '-1') {
        params.type = this.type
      }

      params[this.subType] = this.subTypeVal

      if (this.status != '-1') {
        params.status = this.status
      }

      if (this.lineType != '-1') {
        params.line = this.lineType
      }

      if (this.areaVal != '-1') {
        params.area = this.areaVal
      }

      if (this.areaVal != -1) {
        params.area = this.areaVal
      }

      if (this.cityVal != -1) {
        params.area = this.cityVal
      }

      return params
    },
  },
  methods: {
    onCancel() {
      this.visible = false
    },
    onConfirm() {
      this.confirmEnable()
    },
    getInstanceType(type) {
      return this.typeOptions.filter((item) => {
        if (item.value == type) {
          return true
        }
      })[0].label
    },
    getLineType(type) {
      let line = this.lineTypeOptions.filter((item) => {
        if (item.value == type) {
          return true
        }
      })

      if (line.length) {
        return line[0].label
      }

      return ''
    },
    //  防护域名数
    getPortSiteCount(ips = []) {
      return ips.reduce((sum, item) => {
        return sum + Number(item.port_count != null ? item.port_count : item.site_count)
      }, 0)
    },

    fetch() {
      if(this.loading) return
      this.loading = true
      ddos.list(this.query).then(res => {
        this.loading = false
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.total = res.total
        this.tableData = res.list
      })
    },
    showEnable({ id }) {
      this.visible = true
      this.editId = id
      this.areas2 = []
      this.areaVal2 = -1
      this.cityVal2 = -1
      this.dialogLoading = true

      ddos.availArea(id).then(res => {
        this.dialogLoading = false

        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }

        this.areas2 = res.areas
        if (res.areas.length) {
          let area = res.areas[0]
          let children = area.children

          if (area) {
            this.areaVal2 = area.value
            if (children) {
              this.cityVal2 = children[0].value
            } else {
              this.cityVal2 = -1
            }
            this.confirmDisabled = false
          }
          return
        }

        this.areaVal2 = -1
        this.cityVal2 = -1
        this.confirmDisabled = true
      })
    },
    confirmEnable() {
      ddos.active(this.editId, this.enableArea).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.$message({
          type: 'success',
          message: '实例已启用',
          duration: 1000,
          onClose: () => {
            this.fetch()
            this.visible = false
          }
        })
      })
    }
  },
  created() {
    this.fetch()
  }
}
</script>
<style lang="less">
.vt {
  vertical-align: top;
}
.lbl-text {
  float:left;
  margin-top: 7px;
  width: 70px;
  line-height: 1;
  text-align: justify;
  text-justify: distribute-all-lines;
  text-align-last: justify;
  -moz-text-align-last: justify;
  -webkit-text-align-last: justify;
}
@media screen and (-webkit-min-device-pixel-ratio:0){
  .lbl-text:after {
    content: '';
    display: inline-block;
    width: 100%;
    overflow: hidden;
    height: 0;
  }
}
.high-ip {
  .el-table {
    li {
      font-size: 12px;
    }
  }
  td {
    padding: 20px 0;
    vertical-align: top;
    background-color: #f9f9f9;
  }
  .cell {
    padding-left: 20px;
    padding-right: 20px;
  }
  &-search {
      width: 350px;
      .el-select {
        width: 120px
      }
  }
  .x-radio {
      margin-right: 2px;
  }
  .status-enable {
    position: relative;
    display: inline-block;
    line-height: 20px;
    font-size: 12px;
    text-align: center;
    color: #4CAF50;
    &::before {
      content: '';
      display: inline-block;
      width: 10px;
      height: 10px;
      margin-right: 3px;
      vertical-align: baseline;
      background: #4CAF50;
      border-radius: 50%
    }
  }
}
</style>

