const TheLayout = resolve => require(["@/pages/layout/TheLayout"], resolve)
const AppLogin = resolve => require(["@/pages/login/AppLogin"], resolve)
const AppRegister = resolve => require(["@/pages/login/AppRegister"], resolve)
const AppError401 = resolve => require(["@/pages/error/AppError401"], resolve)
const AppError404 = resolve => require(["@/pages/error/AppError404"], resolve)

const Home = resolve => require(["@/pages/home/Home"], resolve)
const FormInfor = resolve => require(["@/pages/forms/FormInfor"], resolve)
const FormEdit = resolve => require(["@/pages/forms/FormEdit"], resolve)
const Reply = resolve => require(["@/pages/reply/Reply"], resolve)
//const Question = resolve => require(["@/pages/reply/Question"], resolve)
const Consult = resolve => require(["@/pages/reply/Consult"], resolve)
const UserPassword = resolve => require(["@/pages/user/UserPassword"], resolve)
const Charts = resolve => require(["@/pages/charts/Charts"], resolve)
const Push = resolve => require(["@/pages/push/Push"], resolve)

/* 静态页面路由 */
const staticRouter = [
  {
    path: '/',
    redirect: '/index'
  }, 
  {
    path: '/login',
    name: 'login.login',
    component: AppLogin
  }, 
  {
    path: '/register',
    name: 'login.register',
    component: AppRegister
  }, 
  
  {
    path: '/',
    component: TheLayout,
    menu: true,
    children: [
      {
        path: '/index',
        alias: '/home',
        name: 'menu.home',
        icon: 'el-icon-menu',
        component: Home
      }
    ]
  }, 
  {
    path: '/',
    component: TheLayout,
    menu: true,
    name: 'menu.form',
    icon: 'el-icon-tickets',
    children: [
      {
        path: '/forms/base',
        name: 'menu.formBase',
        component: FormInfor
      }, {
        path: '/forms/edit',
        name: 'menu.richText',
        component: FormEdit
      }
    ]
	}, 
  {
    path: '/',
    component: TheLayout,
    icon: 'el-icon-bell',
    name: 'menu.comment',
    menu: true,
    children: [
      {
        path: '/reply',
        name: 'menu.reply',
        component: Reply
      },
      // {
      //   path: '/question',
      //   name: 'menu.question',
      //   component: Question
      // },
      {
        path: '/consult',
        name: 'menu.consult',
        component: Consult
      }
    ]
  },
  {
    path: '/',
    component: TheLayout,
    menu: true,
    name: 'menu.settings',
    children: [
      {
        path: '/user/password',
        name: 'menu.modifyPass',
        icon: 'el-icon-setting',
        component: UserPassword
      }
    ]
	}, 

{
    path: '/',
    component: TheLayout,
    menu: true,
    children: [
      {
        path: '/charts',
        name: 'menu.chart',
        icon: 'el-icon-picture',
        component: Charts
      }
    ]
}, 
{
  path: '/',
  component: TheLayout,
  menu: true,
  children: [
    {
      path: '/push',
      name: 'menu.push',
      icon: 'el-icon-message',
      component: Push
    }
  ]
}, 
  
  {
    path: '/error/401',
    name: 'error.401',
    meta: {errorPage: true},
    component: AppError401
  }, 
  {
    path: '*',
    name: 'error.404',
    meta: {errorPage: true},
    component: AppError404
  }
]

export default staticRouter
