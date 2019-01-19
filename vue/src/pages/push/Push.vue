<template>
  <div>
    <el-card>
      <el-table
        :data="tableData.slice((currentPage-1)*pageSize,currentPage*pageSize)"
        empty-text="暂无推送"
        style="width: 100%">
        <el-table-column label="用户名" width="180">
          <template slot-scope="scope">
            <el-popover trigger="hover" placement="top">
              <p>用户名: {{ scope.row.name }}</p>
              <p>已加盟</p>
              <!--<p>住址: {{ scope.row.address }}</p>-->
              <div slot="reference" class="name-wrapper">
                <el-tag size="medium">{{ scope.row.name }}</el-tag>
              </div>
            </el-popover>
          </template>
        </el-table-column>
        
        <el-table-column label="联系方式">
          <template slot-scope="scope">
            <i class="el-icon-mobile-phone"></i>
            <span style="margin-left: 10px">{{ scope.row.date }}</span>
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
		            <el-input v-model="form.name" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="11">
		          <el-form-item label="咨询时间" label-width="120px">
		            <el-input v-model="form.date" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="24">
		          <el-form-item label="咨询内容" label-width="120px">
		            <el-input v-model="form.content" auto-complete="off" readonly></el-input>
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
      form: {
        name: '',
        date: '',
        content: '不知道说什么，给您拜个早年吧',
        reply: '',
        index: 0
      }
    }
  },
  created:function(){
    this.$axios.get('http://adminc.ah123.top/usermessages/advice').then(data=>{
      console.log(data)
    }).catch(err=>{

    })
  },
  mounted() {
//	requestUserQuery(this.formInline).then(res => {
//    this.pageTotal = res.data.length
//    this.tableData = res.data
//  })
  },
  methods: {
    handleEdit (index, row) {
      this.form.index = index + (this.currentPage - 1) * this.pageSize
      this.form.name = row.name
      this.form.date = row.date
      this.form.address = row.address
      this.dialogFormVisible = true
    },
    handleSizeChange (size) {
      this.pageSize = size
    },
    handleCurrentChange (currentPage) {
      this.currentPage = currentPage
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
  	},
  }
}
</script>

<style>
.el-dialog{ width: 100%; max-width: 960px; }
</style>
