<template>
  <div class="m-home">
    <el-row :gutter="20">
    	<el-col :xs="24" :sm="12" :lg="6">
        <el-card class="m-box-card" shadow="hover">
          <div class="m-icon">
            <i class="fa fa-address-card-o" style="color: #E6A23C;"></i>
          </div>
          <div class="m-content">
            <p>{{$t('home.hellow')}}</p>
            <p class="m-count">{{ user.name }}</p>
          </div>
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="m-box-card" shadow="hover">
          <div class="m-icon">
            <i class="fa fa-envelope-o fa-fw" style="color: #E6A23C;"></i>
          </div>
          <div class="m-content">
            <p>{{$t('home.system')}}</p>
            <p class="m-count">{{ info.message?info.message:'0' }}</p>
          </div>
        </el-card>
      </el-col>
      <!--<el-col :xs="24" :sm="12" :lg="6">
        <el-card class="m-box-card" shadow="hover">
          <div class="m-icon">
            <i class="fa fa-snowflake-o" style="color: #409EFF;"></i>
          </div>
          <div class="m-content">
            <p>天气</p>
            <p>{{ info.weather }}</p>
          </div>
        </el-card>
      </el-col>-->
    </el-row>
    
    <el-row :gutter="20">
    	<el-col :span="5">
        <el-select v-model="selectTime" :placeholder="$t('home.selectYear')">
		      <el-option :label="$t('home.selectDay')" value="day"></el-option>
		      <el-option :label="$t('home.selectMounth')" value="mounth"></el-option>
		      <el-option :label="$t('home.selectYear')" value="year"></el-option>
		    </el-select>
      </el-col>
      <el-col :span="5">
		    <el-date-picker
		      v-model="selectTime"
		      type="daterange"
		      start-placeholder="开始日期"
		      end-placeholder="结束日期">
		    </el-date-picker>
      </el-col>
    </el-row>
    
    <el-row :gutter="20">
      <el-col :sm="24" :lg="18">
        <el-card class="m-box-card" shadow="hover">
          <ve-line :data="whichLine" :settings="chartSettings1"></ve-line>
        </el-card>
      </el-col>
      <el-col :sm="24" :lg="6">
        <el-row :gutter="20" align>
          <el-col :sm="12" :lg="24">
            <el-card class="m-box-card" shadow="hover"
                     style="height: 215px;background-color: rgb(143, 201, 251);color:#ffffff;">
              <!-- <div slot="header"> -->
                <p style="text-align: center">
                  <i class="fa fa-address-card-o" style="color: #F56C6C;font-size: 35px;"></i>
                </p>
                <div style="padding-top: 10px;">
                  <p>{{$t('home.user')}}：{{ user.name }}</p >
                  <br />
                  <p>{{$t('home.time')}}：</p >
                  <p>{{ user.time }}</p >
                  <br />
                  <p>{{$t('home.lastTime')}}：</p >
                  <p>{{ user.lastTime?user.lastTime:'欢迎首次登陆！' }}</p >
                </div>
              <!-- </div> -->
              
            </el-card>
          </el-col>
          <el-col :sm="12" :lg="24">
            <el-card class="m-box-card" shadow="hover">
              <div style="height: 215px; margin: -20px;background-color: rgb(247, 151, 214);color:#ffffff;">
                <vue-scroll>
                  <div style="padding: 20px;">
                    <p style="font-weight: bold;text-align: center">{{$t('home.importent')}}</p>
                    <p v-if='tongzhi'>{{tongzhi}}</p>
                    <p v-else>暂无通知</p>
                    <!-- <p v-for="index in 20" :key="index">{{index}}. 帅哥/美女出没，请注意！</p>
                    <p>~(˘▾˘~)~(˘▾˘~)</p>
                    <p>对面的帅哥/美女，你好啊。</p> -->
                  </div>
                </vue-scroll>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import VeLine from 'v-charts/lib/line.common'
import axios from 'axios'

export default {
  name: 'FuncHome',
  components: {
    VeLine,
  },
  computed: {
  	whichLine () {
  		if(this.selectTime == 'day'){
  			return this.chartData1
  		}else if(this.selectTime == 'mounth'){
  			return this.chartData2
  		}else if(this.selectTime == 'year'){
  			return this.chartData3
  		}else{
  			return this.chartData3
  		}
  	}
  },
  data () {
    return {
      tongzhi:'',
    	selectTime: 'day',
      info: {
        // message: parseFloat(6).toLocaleString(),
        message: '0',
      },
      user: {
        name: 'Admin',
        create_time: '2018-12-19 12:00:00',
        loginIp: '172.28.12.34',
        lastTime: '2018-01-01 12:00:00',
        lastIp: '172.28.12.34'
      },
      chartData1: {
        columns: ['day', 'access', 'advice' ],
        rows: [
          {'日期': '1/1', '访问用户': 21, '咨询用户': 15 },
          {'日期': '1/2', '访问用户': 20, '咨询用户': 16 },
          {'日期': '1/3', '访问用户': 45, '咨询用户': 17 },
          {'日期': '1/4', '访问用户': 22, '咨询用户': 15 },
          {'日期': '1/5', '访问用户': 71, '咨询用户': 25 },
          {'日期': '1/6', '访问用户': 35, '咨询用户': 19 }
        ]
      },
      chartData2: {
        columns: ['day', 'access', 'advice' ],
        rows: [
          {'日期': '1日', '访问用户': 444, '咨询用户': 44 },
          {'日期': '2日', '访问用户': 456, '咨询用户': 45 },
          {'日期': '3日', '访问用户': 456, '咨询用户': 45 },
          {'日期': '4日', '访问用户': 654, '咨询用户': 65 },
          {'日期': '5日', '访问用户': 345, '咨询用户': 34 },
          {'日期': '6日', '访问用户': 453, '咨询用户': 45 },
          {'日期': '7日', '访问用户': 464, '咨询用户': 46 },
          {'日期': '8日', '访问用户': 564, '咨询用户': 56 },
          {'日期': '9日', '访问用户': 564, '咨询用户': 56 },
          {'日期': '10日', '访问用户': 645, '咨询用户': 64 },
          {'日期': '11日', '访问用户': 456, '咨询用户': 45 },
          {'日期': '12日', '访问用户': 546, '咨询用户': 54 },
          {'日期': '13日', '访问用户': 644, '咨询用户': 64 },
          {'日期': '14日', '访问用户': 654, '咨询用户': 65 },
          {'日期': '15日', '访问用户': 456, '咨询用户': 45 },
          {'日期': '16日', '访问用户': 546, '咨询用户': 54 },
          {'日期': '17日', '访问用户': 564, '咨询用户': 56 },
          {'日期': '18日', '访问用户': 645, '咨询用户': 64 },
          {'日期': '19日', '访问用户': 536, '咨询用户': 53 },
          {'日期': '20日', '访问用户': 635, '咨询用户': 63 },
          {'日期': '21日', '访问用户': 756, '咨询用户': 75 },
          {'日期': '22日', '访问用户': 345, '咨询用户': 34 },
          {'日期': '23日', '访问用户': 456, '咨询用户': 45 },
          {'日期': '24日', '访问用户': 546, '咨询用户': 54 }
          
        ]
      },
      chartData3: {
        columns: ['日期', '访问用户', '咨询用户' ],
        rows: [
          {'日期': '1月', '访问用户': 4454, '咨询用户': 1445 },
          {'日期': '2月', '访问用户': 4556, '咨询用户': 1455 },
          {'日期': '3月', '访问用户': 6636, '咨询用户': 1663 },
          {'日期': '4月', '访问用户': 7377, '咨询用户': 1737 },
          {'日期': '5月', '访问用户': 8878, '咨询用户': 1887 },
          {'日期': '6月', '访问用户': 9888, '咨询用户': 1988 }
        ]
      },
      chartSettings1: {
				labelMap: {
          'day': '日期',
          'advice': '咨询用户',
          'access': '访问用户'
        },      	
        yAxisType: ['normal'],
        yAxisName: ['数值']
      },
    }
  },
  created:function(){
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
        this.$router.push('/login')
      }else{
        this.$axios.get('api/index/index/pid/'+CookieData.pid).then(data=>{
          const $data = data.data;
          console.log($data);
          this.chartData1.rows = $data.chartData1;
          this.chartData2.rows = $data.chartData2;
        })

      }
    }).catch(err=>{
      
    })
  },
  methods: {
    tableRowClassName ({row, rowIndex}) {
      if (rowIndex === 1) {
        return 'warning-row'
      } else if (rowIndex === 3) {
        return 'success-row'
      }
      return ''
    },
  },
  watch: {
  	selectTime (val, oldval) {
//		this.$axios.post('',val).then(data => {
//			
//		}).catch(err => {
//      console.log(err)
//    })
  	}
 },
 mounted () {
// 	console.log('一个人悄咪咪的看！！！！！！        www.genwohuijia.top/hhhhh   打开后稍微用一下开发者工具')
 }
}
</script>

<style scoped lang="scss">
.m-home {
  .m-box-card {
    margin-bottom: 10px;
    color: #666666;
    .m-icon {
      float: left;
      width: 60px;
      i {
        font-size: 40px;
      }
    }
    .m-content {
      margin-left: 60px;
      .m-count {
        font-size: 20px;
      }
    }
  }
  .m-task-box {
    margin-bottom: 20px;
    .m-task-text {
      float: left;
      line-height: 16px;
    }
    .m-task-pro {
      margin-left: 60px;
    }
  }
}

</style>
<style>
	
.el-input{ margin-bottom:10px; }
.el-table .warning-row {
  background: oldlace;
}

.el-table .success-row {
  background: #f0f9eb;
}
</style>
