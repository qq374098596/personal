<template>
  <div>
    <el-card>
      <el-form :inline="true" :model="formInline">
        <el-form-item label="内容：" prop="param1">
          <el-input v-model="formInline.param1" placeholder="条件1"></el-input>
        </el-form-item>
        <el-form-item label="评价：" prop="param2">
          <el-select v-model="formInline.param2" placeholder="条件2">
            <el-option label="好评" value="3"></el-option>
            <el-option label="中评" value="2"></el-option>
            <el-option label="差评" value="1"></el-option>
          </el-select>	
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSubmit('formInline')">查询</el-button>
          <el-button type="primary">导出</el-button>
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
        
        <el-table-column label="评论内容">
          <template slot-scope="scope">
            <span style="margin-left: 10px">{{ scope.row.content}}</span>
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
		          <el-form-item label="评论时间" label-width="120px">
		            <el-input v-model="form.date" auto-complete="off" readonly></el-input>
		          </el-form-item>
	          </el-col>
	          <el-col :span="24">
		          <el-form-item label="评论内容" label-width="120px">
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
	          <el-button type="primary" @click="ReplyClick(form.id)">确 定</el-button>
	        </div>
	      </el-dialog>
	     </el-col>
    </el-card>
  </div>
</template>

<script>
// import {requestUserQuery} from '@/api/user'

export default {
  name: 'Reply',
  data () {
    return {
      formInline: {
        param1: '',
        param2: ''
      },
      tableData: [],
      currentPage: 1,
      pageSize: 10,
      pageTotal: 0,
      dialogFormVisible: false,
      form: {},
    }
  },
  created:function(){
    this.$axios.get('api/Usermessages/review').then(data=>{
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
    onSubmit (formName) {
    	var neirong = this.formInline.param1
    	var pingjia = this.formInline.param2
      if (neirong != '' || pingjia != '') {
        this.$axios.post('api/Usermessages/reviewSearch',{content:neirong,num:pingjia}).then(data => {
          const $data = data.data;
          // this.$message({
          //   message: '查询成功！',
          //   type: 'success'
          // })
          this.pageTotal = $data.length;
          this.tableData = $data;
        })
      }
    },
    handleEdit (index, row) {
      this.form.index = index + (this.currentPage - 1) * this.pageSize
      this.form.name = row.nickname
      this.form.date = row.time
      this.form.content = row.content
      this.form.reply = ''
      this.form.id = row.id
      this.dialogFormVisible = true
    },
    handleDelete (index, row) {
      this.tableData.splice(index + (this.currentPage - 1) * this.pageSize, 1)
      this.pageTotal = this.tableData.length
      this.$message({
        message: '删除' + row.name + '成功！',
        type: 'success'
      })
    },
    handleSizeChange (size) {
      this.pageSize = size
    },
    handleCurrentChange (currentPage) {
      this.currentPage = currentPage;
      this.$emit('update')
    },
    ReplyClick (mid) {
      var CookieVal = document.cookie.split('; ');
      var adminVal;
      for(var i = 0; i < CookieVal.length; i++){
        var arr = CookieVal[i].split("=");
        if('api' == arr[0]){
          adminVal = arr[1];
        }
      }
      var CookieUn = unescape(unescape(adminVal)).substring(6);
      var CookieData = JSON.parse(CookieUn);
    
      this.user = CookieData;
      var uid = this.user.id;
      this.$axios.post('api/Usermessages/addreview',{
        uid1:uid,
        mid:mid,
        content:this.form.reply
      }).then(data=>{
        console.log(data);
        const $data = data.data;
        if ($data.s == 500) {
          alert($data.error);
        }else{
          alert($data.msg);
          this.$router.push('/reply')
        }
      })
      // this.dialogFormVisible = false
      // this.$message({
      //   message: '回复' + this.form.name + '成功！',
      //   type: 'success'
      // })
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
