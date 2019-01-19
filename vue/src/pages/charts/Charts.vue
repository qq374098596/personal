<template>
  <div>
    <el-card>
      <el-tabs v-model="activeName">
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
        <el-tab-pane :label="$t('charts.line')" name="first" :lazy="true">
          <el-card shadow="hover">
            <ve-line :data="whichLine" :settings="chartSettings1"></ve-line>
          </el-card>
        </el-tab-pane>
        <el-tab-pane :label="$t('charts.bar')" name="second" :lazy="true">
          <el-card shadow="hover">
            <ve-histogram :data="whichLine" :settings="chartSettings1"></ve-histogram>
          </el-card>
        </el-tab-pane>
        <el-tab-pane :label="$t('charts.pie')" name="tired" :lazy="true">
          <el-card shadow="hover">
            <ve-pie :data="chartData3" :settings="chartSettings3"></ve-pie>
          </el-card>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script>
import VeLine from 'v-charts/lib/line.common'
import VeHistogram from 'v-charts/lib/histogram.common'
import VePie from 'v-charts/lib/pie.common'

export default {
  name: 'FuncCharts',
  components: {
    VeLine,
    VeHistogram,
    VePie
  },
  created () {
  	console.log(document.body.scrollTop)
  },
  computed: {
  	whichLine () {
  		if(this.selectTime == 'day'){
  			return this.chartData11
  		}else if(this.selectTime == 'mounth'){
  			return this.chartData12
  		}else if(this.selectTime == 'year'){
  			return this.chartData13
  		}else{
  			return this.chartData13
  		}
  	}
  },
  data () {
    return {
    	selectTime: 'day',
      activeName: 'first',
      chartData11: {
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
      chartData12: {
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
      chartData13: {
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
      chartData3: {
        columns: ['名字', '份数'],
        rows: [
          { '名字': 'ming', '份数': 99 },
          { '名字': 'pan', '份数': 88 },
          { '名字': 'pei', '份数': 77 },
          { '名字': 'fan', '份数': 66 },
          { '名字': 'peng', '份数': 55 },
          { '名字': 'le', '份数': 44 }
        ]
      },
      chartSettings3: {
        limitShowNum: 5
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
          this.chartData11.rows = $data.chartData1;
          this.chartData12.rows = $data.chartData2;
        })

      }
    }).catch(err=>{
      
    })
  }
}
</script>

<style scoped>

</style>
