import {request} from '../utils/request'

export const requestLogin = params => {
  return request('/api/user/login', params).then(data => {
    localStorage.setItem('user-token', JSON.stringify(data.token))
    return data
  })
}

export const requestRegister = params => {
  return request('/api/user/register', params)
}

export const requestUserInfo = params => {
  return request('http://adminc.ah123.top/api/user/info', params).then((data) => {
    sessionStorage.setItem('user-info', JSON.stringify(data))
    return data
  })
}

export const requestLogout = params => {
  return request('http://adminc.ah123.top/user/logout', params)
}

export const requestChangePassword = params => {
  return request('/api/user/changePassword', params).then(data => {
    localStorage.setItem('user-token', JSON.stringify(data.token))
    return data
  })
}

export const requestUserQuery = params => {
  return request('http://adminc.ah123.top/user/query', params)
}

export const requestPermissionsQuery = params => {
  return request('/api/user/permissions', params)
}
