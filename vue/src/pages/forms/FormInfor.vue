<template>
  <div>
    <el-card>
      <el-tabs v-model="activeName">
        <el-tab-pane label="资料信息" name="first">
          <el-card shadow="hover" style="width: 100%;">
            <el-form ref="brand" :model="brand" :rules="rules" label-width="80px" class="demo-ruleForm">
    	
					  <el-form-item label="品牌名称" prop="name" style="border: none;">
					  	<el-col :span="11" :xs="24">
					  		<el-input v-model="brand.name" readonly></el-input>
					  	</el-col>
					  </el-form-item>
					  
					  <el-form-item label="品牌行业" prop="profession">
					  	<el-col :span="11" :xs="24">
					  		<el-input v-model="brand.profession" readonly></el-input>
					  	</el-col>
					    <!--<el-select v-model="brand.profession" placeholder="请选择品牌行业">
					      <el-option label="行业1" value="hangye1"></el-option>
					      <el-option label="行业2" value="hangye2"></el-option>
					    </el-select>-->
					  </el-form-item>
					  
					  <el-form-item label="公司名称" prop="company">
					  	<el-col :span="11" :xs="24">
					  		<el-input v-model="brand.company"></el-input>
					  	</el-col>
					  </el-form-item>
					  
					  <el-form-item label="加盟费用" prop="jiamengfei">
					  	<el-col :span="11" :xs="24">
					  		<el-input  placeholder="加盟费" v-model.number="brand.jiamengfei"></el-input>
					  	</el-col>
					  </el-form-item>
					  
					  <el-form-item label="联系方式" prop="tel">
					  	<el-col :span="11" :xs="24">
					  		<el-input v-model.number="brand.tel"></el-input>
					  	</el-col>
					  </el-form-item>
					  
					  <el-form-item label="创建时间" prop="date1">
					    <el-col :span="11" :xs="24">
					      <el-date-picker type="date" placeholder="选择日期" v-model="brand.time" style="width: 100%;"></el-date-picker>
					    </el-col>
					  </el-form-item>
					  
					  <el-form :inline="true" class="demo-form-inline">
						  <el-form-item label="加盟门店" prop="joinShop">
						    <el-input  placeholder="加盟门店" v-model="brand.jiamengshop"></el-input>
						  </el-form-item>
						  <el-form-item label="直营门店" prop="directShop">
						    <el-input  placeholder="直营门店" v-model="brand.zhiyingshop"></el-input>
						  </el-form-item>
						</el-form>
			
					  
					  <el-form-item>
					    <el-button type="primary" @click="onSubmit">保存</el-button>
					    <!--<el-button>取消</el-button>-->
					  </el-form-item>
					</el-form>
          </el-card>
        </el-tab-pane>
        <el-tab-pane label="上传图册" name="second">
          <!-- <el-card shadow="hover">
            <el-upload
              class="upload-demo"
              action="https://jsonplaceholder.typicode.com/posts/"
              multiple
              :limit="3"
              :file-list="fileList">
              <el-button size="small" type="primary">点击上传</el-button>
              <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
            </el-upload>
          </el-card>
          <br/> -->
          <el-upload
            class="upload-demo"
            action="api/upload/uploads"
            :on-preview="handlePreview"
            :on-remove="handleRemove"
            :on-change="handleChange"
            :file-list="fileList"

            list-type="picture">
            <el-button size="small" type="primary">点击上传</el-button>
            <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过500kb</div>
          </el-upload>
        </el-tab-pane>
        <!--<el-tab-pane label="选择轮播图" name="third">
          <el-card shadow="hover">
          	<el-transfer
					    v-model="value2"
					    :props="{
					      key: 'name',
      					label: 'desc'
					    }"
					    :data="data2">
					  </el-transfer>
          </el-card>
        </el-tab-pane>-->
      </el-tabs>
    </el-card>
    <el-button type="primary" @click="onImgSubmit" style="margin-top: 20px; float: right;">提交</el-button>
  </div>
</template>

<script>
export default {
  name: 'FuncFormBase',
  data () {
    return {
    	brand: {
        name: '',
        profession: '',
        company: '',
        tel: '',
        time: '',
				jiamengshop: '',
				jiamengfei: '',
				zhiyingshop: ''
      },
      imageurl: [],
      activeName: 'first',
      ruleForm: {
        name: '',
        region: '',
        date1: '',
        date2: '',
        delivery: false,
        type: [],
        resource: '',
        desc: ''
      },
      rules: {
        company: [
        	{ required: true, message: '请输入公司名称', trigger: 'blur' }
        ],
        jiamengfei: [
        	{ required: true, message: '请输入加盟费用，单位为：万' },
        	{ type: 'number', message: '请填写数字' }
        ],
        tel: [
        	{ required: true, message: '请输入联系方式', trigger: 'blur' },
        	// { type: 'number', message: '电话必须为数字值' }
        ],
        time: [
          { required: true, message: '请选择日期', trigger: 'change' }
        ]
        
      },
      //上传图片路径
      fileList: [
      ],
      
    }
  },
  created:function(){
  	//console.log(document.body.scrollTop)
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
    var CookieToken = CookieData.token;
    this.$axios.post('api/index/islogin',{token:CookieToken}).then(data=>{
      const $data = data.data;
      if($data.s==500){
        alert($data.error)
        this.$router.push('/login')
      }else{
        this.$axios.get('api/froms/brand').then(data=>{
          const $data = data.data.data;
          this.brand = $data.brandInfo;
          for(var i=0;i<$data.fileList.length;i++){
            this.fileList.push({
              url: $data.fileList[i]
            })
          }
        }).catch(err=>{

        })
      }
    }).catch(err=>{
      console.log(err)
    })
  },
  methods: {
    onSubmit (formName) {
      console.log(formName);
      this.$axios.post('api/froms/upBrand',
        {
          pid:this.user.pid,
          company:this.brand.company,
          joinMoney:this.brand.jiamengfei,
          tel:this.brand.tel,
          joinShop:this.brand.jiamengshop,
          directShop:this.brand.zhiyingshop
        }
      ).then(data=>{
        console.log(data);
      })
      // this.$refs[formName].validate((valid) => {
      //   if (valid) {
      //     alert(11)
      //     // this.$axios.post('api/froms/upBrand',{}).then(data=>{
      //     //   console.log(data)
      //     // }).catch(err=>{

      //     // })
      //   } else {
      //     console.log('error submit!!')
      //     return false
      //   }
      // })
    },
    onImgSubmit:function(){
      this.$axios.post('api/froms/upImgList',{pid:this.user.pid,imglist:this.imageurl}).then(data=>{
        const $data = data.data;
        if ($data.s == 500) {
          alert($data.error);
        }else{
          alert($data.msg);
          this.$router.push('/forms/base')
        }
      })
      //console.log(this.imageurl);
    },
    handleChange(file, fileList) {
      this.fileList = fileList.slice(-3);
      if (file.response) {
        this.imageurl.push(file.response.data.join(''));
      }
    },
    handleRemove(file, fileList) {
      console.log(file, fileList);
    },
    handlePreview(file) {
      console.log(file);
    },
    resetForm (formName) {
      this.$refs[formName].resetFields()
    },
    handleExceed (files, fileList) {
      this.$message.warning('当前限制选择 3 个文件，本次选择了' + files.length +
        ' 个文件，共选择了 ' + (files.length + fileList.length) + ' 个文件'
      )
    },
    beforeRemove (file, fileList) {
      return this.$confirm('确定移除 ' + file.name + '}？')
    }
  }
}
</script>

<style scoped>

</style>
