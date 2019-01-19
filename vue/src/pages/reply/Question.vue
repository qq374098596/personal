<template>
  <div>
    <el-card>
      <el-form :inline="true" :model="formInline" ref="formInline">
        <el-form-item label="内容：" prop="param1">
          <el-input v-model="formInline.param1" placeholder="条件1"></el-input>
        </el-form-item>
        <!--<el-form-item label="问题类型：" prop="param2">
          <el-select v-model="formInline.param2" placeholder="条件2">
            <el-option label="收益" value="item1"></el-option>
            <el-option label="成本" value="item2"></el-option>
            <el-option label="差评" value="item3"></el-option>
          </el-select>	
        </el-form-item>-->
        <el-form-item>
          <el-button type="primary" @click="onSubmit('formInline')">查询</el-button>
          <el-button type="primary" @click="downExcel">导出</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card style="margin-top: 20px;">
      <el-table
        :data="tableData.slice((currentPage-1)*pageSize,currentPage*pageSize)"
        style="width: 100%">
        <el-table-column label="日期" width="150">
          <template slot-scope="scope">
            <i class="el-icon-time"></i>
            <span style="margin-left: 10px">{{ scope.row.date }}</span>
          </template>
        </el-table-column>
        
        <el-table-column label="用户名" width="120">
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
        
        <el-table-column label="询问内容">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.name+'在'+scope.row.date+'说：不知道说什么，给您拜个早年吧' }}</span>
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
        :page-sizes="[5,10, 20, 30, 40]"
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
		          <el-form-item label="询问时间" label-width="120px">
		            <el-input v-model="form.date" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="24">
		          <el-form-item label="询问内容" label-width="120px">
		            <el-input v-model="form.content" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="24">
		          <el-form-item label="回答" label-width="120px">
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
      formInline: {
        param1: '',
        param2: '选择评价'
      },
      tableData: [],
      currentPage: 1,
      pageSize: 5,
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
    this.$axios.get('api/Usermessages/question').then(data=>{
      console.log(data)
    }).catch(err=>{

    })
  },
  mounted() {
  	requestUserQuery(this.formInline).then(res => {
      this.pageTotal = res.data.length
      this.tableData = res.data
    })
  },
  methods: {
  	downExcel () {
      const th = ['时间', '用户名', '地址']
      const filterVal = ['date', 'name', 'address']
      const data = this.tableData.map(v => filterVal.map(k => v[k]))
      const [fileName, fileType, sheetName] = ['问答', 'xlsx', '问答页']
      this.$toExcel({th, data, fileName, fileType, sheetName})
    },
    onSubmit (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          requestUserQuery(this.formInline).then(res => {
            this.$message({
              message: '查询成功！',
              type: 'success'
            })
            this.pageTotal = res.data.length
            this.tableData = res.data
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
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
      this.currentPage = currentPage;
      this.$emit('update')
    },
    ReplyClick () {
      this.dialogFormVisible = false
      this.$message({
        message: '回复' + this.form.name + '成功！',
        type: 'success'
      })
    }
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

<style scoped>
.el-dialog{ width: 100%; max-width: 960px; }
form>.el-form-item{ margin-bottom: 0 !important; }
</style>
