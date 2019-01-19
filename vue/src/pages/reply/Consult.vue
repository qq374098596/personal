<template>
  <div>

    <el-card>
    	<el-button type="primary" style="float: right;" @click="downExcel">导出</el-button>
      <el-table
        :data="tableData.slice((currentPage-1)*pageSize,currentPage*pageSize)"
        style="width: 100%">
        <el-table-column label="ID" width="150">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.id }}</span>
          </template>
        </el-table-column>

        <el-table-column label="日期" width="150">
          <template slot-scope="scope">
            <i class="el-icon-time"></i>
            <span style="margin-left: 10px">{{ scope.row.time }}</span>
          </template>
        </el-table-column>
        
        <el-table-column label="用户名" width="120">
          <template slot-scope="scope">
            <el-popover trigger="hover" placement="top">
              <p>用户名: {{ scope.row.nickname }}</p>
              <p>已加盟</p>
              <!--<p>住址: {{ scope.row.address }}</p>-->
              <div slot="reference" class="name-wrapper">
                <el-tag size="medium">{{ scope.row.nickname }}</el-tag>
              </div>
            </el-popover>
          </template>
        </el-table-column>

        <el-table-column label="姓名">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.username}}</span>
          </template>
        </el-table-column>

        <el-table-column label="手机">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.phone}}</span>
          </template>
        </el-table-column>

        <el-table-column label="意向城市">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.city}}</span>
          </template>
        </el-table-column>
        
        <el-table-column label="咨询内容">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.message}}</span>
          </template>
        </el-table-column>

        <el-table-column label="投资预算">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.budget}}W</span>
          </template>
        </el-table-column>
        
        <el-table-column label="操作" width="100">
          <template slot-scope="scope" :span="1">
            <el-button
              size="mini"
              @click="handleEdit(scope.$index, scope.row)">查看
            </el-button>

          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        background
        :current-page="currentPage"
        :page-sizes="[10, 20, 30, 40]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="pageTotal">
      </el-pagination>
      <el-col :span="12" :xs="24">
	      <el-dialog title="评论信息" :visible.sync="dialogFormVisible">
	        <el-form :model="form">
	        	<el-col :span="11">
		          <el-form-item label="用户名" label-width="120px">
		            <el-input v-model="form.nickname" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="11">
		          <el-form-item label="咨询时间" label-width="120px">
		            <el-input v-model="form.time" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="24">
		          <el-form-item label="咨询内容" label-width="120px">
		            <el-input v-model="form.message" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="24">
		          <el-form-item label="回复内容" label-width="120px">
		            <el-input v-model="form.reply" auto-complete="off"></el-input>
		          </el-form-item>
	          </el-col>
	        </el-form>
	        <div slot="footer" class="dialog-footer">
	          <el-button @click="dialogFormVisible = false">取 消</el-button>
	          <el-button type="primary" @click="ReplyClick()">确 定</el-button>
	        </div>
	      </el-dialog>
	     </el-col>
    </el-card>
  </div>
</template>

<script>
import {requestUserQuery} from '@/api/user'

export default {
  name: 'Reply',
  data () {
    return {
      tableData: [],
      currentPage: 1,
      pageSize: 10,
      pageTotal: 0,
      dialogFormVisible: false,
      form: {}
    }
  },
  created:function(){
    this.$axios.get('api/usermessages/advice').then(data=>{
      const $data = data.data;
      console.log($data);
      this.pageTotal = $data.length;
      this.tableData = $data;
    }).catch(err=>{

    })
  },
  // mounted() {
  // 	requestUserQuery(this.formInline).then(res => {
  //     this.pageTotal = res.data.length
  //     this.tableData = res.data
  //   })
  // },
  methods: {
    handleEdit (index, row) {
      this.form.index = index + (this.currentPage - 1) * this.pageSize
      this.form.nickname = row.nickname
      this.form.time = row.time
      this.form.message = row.message
      this.dialogFormVisible = true
    },
    handleSizeChange (size) {
      this.pageSize = size
    },
    handleCurrentChange (currentPage) {
      this.currentPage = currentPage;
      this.$emit('update')
    },
    ReplyClick () {
      this.dialogFormVisible = false
      this.$message({
        message: '回复' + this.form.name + '成功！',
        type: 'success'
      })
    },
    downExcel () {
      const th = ['时间', '用户名', '地址']
      const filterVal = ['date', 'name', 'address']
      const data = this.tableData.map(v => filterVal.map(k => v[k]))
      const [fileName, fileType, sheetName] = ['问答', 'xlsx', '问答页']
      this.$toExcel({th, data, fileName, fileType, sheetName})
    },
  },
  watch: {
  	pageSize (val, oldval) {
  		requestUserQuery(this.formInline).then(res => {
	      this.pageTotal = res.data.length
	      this.tableData = res.data
	    })
  		this.$emit('update')
  	},
  }
}
</script>

<style>
.el-dialog{ width: 100%; max-width: 960px; }
</style>
