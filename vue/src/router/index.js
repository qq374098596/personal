import Vue from 'vue'
import Router from 'vue-router'
import whiteList from './whiteList'
import staticRouter from './staticRouter'
import {requestUserInfo} from '@/api/user'

Vue.use(Router)

const router = new Router({
  routes: staticRouter,
})

/* 利用router.meta保存数据级权限 */
const router_init = (permissions) => {
  permissions.forEach(function (v) {
    let routeItem = router.match(v.path)
    if (routeItem) {
      routeItem.meta.permission = v.permission ? v.permission : []
    }
  })
}

/* 检测用户是否有权限访问页面 */
const page_permission = (permissions, to_path, next) => {
  let allow_page = false
  permissions.forEach(function (v) {
    if (v.path === to_path) {
      allow_page = true
    }
  })
  allow_page ? next() : next({path: '/error/401'})
}

/* 权限控制 */
router.beforeEach((to, from, next) => {
  /* 忽略错误页面的权限判断 */
  if (to.meta.errorPage) {
    return next()
  }
  /* 进入登录页面将注销用户信息 */
  if (to.path === '/login') {
    sessionStorage.removeItem('user-info')
    localStorage.removeItem('user-token')
  }
  /* 免登录页面 */
  if (whiteList.indexOf(to.fullPath) >= 0) {
    return next()
  }
  let user_info = JSON.parse(sessionStorage.getItem('user-info'))
  /* 上次会话结束，重新获取用户信息 */
  if(Cookie.get('api')){
	  if (!user_info) {
      alert('请登陆')
      next('/login')
	  } else {
	    /* 已登录时判断页面权限 */
	    const permissions = user_info.permissions || []
	    page_permission(permissions, to.path, next)
	  }
  }else{
		next('/login')
	}
})
const Cookie = {
	set: function(key, val, expiresDays){//这里传天数
      if (expiresDays){
        //要保存Cookie有效期
        var date = new Date();
        date.setTime(date.getTime()+expiresDays*24*3600*1000);
        var expiresStr = "expires=" + date.toGMTString() + ';' ;
      } else {
        var expiresStr = '';
      }
      //拼接Cookie
      document.cookie = key+'='+escape(val)+';' + expiresStr;//escape()字符串进行编码
    },
    get: function(key){
      var getCookie = document.cookie.replace(/[ ]/g,''); 
      var resArr = getCookie.split(';');
      var res;
      for (var i = 0; i < resArr.length; i++) {
        var arr = resArr[i].split('=');
        if (arr[0] == key){
            res = arr[1];
            break;
        }
      }
      return unescape(res)//解码
//    return res
  	},
}


export default router
