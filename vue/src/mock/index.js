import Mock from 'mockjs'

export default {
  mockData () {
    Mock.mock('http://adminc.ah123.top/user/logout', {
      'success': true,
      'result': {
        'token': 'fdsjfhjkdshfkldsajfjasdfbjsdkfhsdajfj'
      }
    })
    Mock.mock('/api/user/changePassword', {
      'success': true,
      'result': {
        'token': 'fdsjfhjkdshfkldsajfjasdfbjsdkfhsdajfj'
      }
    })
    Mock.mock('http://adminc.ah123.top/user/query', {
      'success': true,
      'result': {
        'data|30-60': [
          {
            'name|1': [Mock.mock('@cname'), Mock.mock('@cname'), Mock.mock('@cname'), Mock.mock('@cname')],
            'date|1': [Mock.mock('@date("yyyy-MM-dd")'), Mock.mock('@date("yyyy-MM-dd")'), Mock.mock('@date("yyyy-MM-dd")')],
            'address|1': [Mock.mock('@county(true)'), Mock.mock('@county(true)'), Mock.mock('@county(true)')]
          }
        ]
      }
    })
    Mock.mock('/api/user/permissions', {
      'success': true,
      'result': {
        'permissions': {
          users: [
            {
              id: 1,
              name: '张三',
              role_ids: [1, 3]
            }, {
              id: 2,
              name: '李四',
              role_ids: [2, 3]
            }, {
              id: 3,
              name: '王五',
              role_ids: [3]
            }
          ],
          roles: [
            {
              id: 1,
              name: '管理员',
              page_ids: [1, 2],
              directive_ids: [1, 2]
            }, {
              id: 2,
              name: '普通用户',
              page_ids: [1, 2],
              directive_ids: [1]
            }, {
              id: 3,
              name: '登录用户',
              page_ids: [1],
              directive_ids: []
            }
          ],
          pages: [
            {
              id: 1,
              name: '表格',
              path: '/tables'
            }, {
              id: 2,
              name: '图表',
              path: '/charts'
            }
          ],
          directive: [
            {
              id: 1,
              name: 'modify',
              page_id: 1
            }, {
              id: 2,
              name: 'delete',
              page_id: 1
            }
          ]
        }
      }
    })
  }
}
