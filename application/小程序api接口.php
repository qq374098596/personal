首页 index

表名称		查询字段
turnimgs	src,id
gushi		title,id
daoshi		*

首页公告
"select from_unixtime(a.time) as date,c.name,b.nickname from fanxiang_dianping as a left join fanxiang_user as b on a.uid=b.id left join fanxiang_pinpai as c on a.pinpaiid=c.id limit 10"

<br>

返乡创业  chuangye/index
chuangyedaoshi
表名称		查询字段
turnimgs	src,id
hangye		*
daoshi		*

daoshixiangqing
表名称		查询字段
daoshi		*

chuangyegushi
表名称		查询字段
gushi		*
dianzan		status

gushixiangqing	评论
'select a.*,b.nickname as user1,b.headimg as headimg1,c.nickname as user2 from fanxiang_pinglun as a left join fanxiang_user as b on a.uid1=b.id left join fanxiang_user as c on a.uid2=c.id where a.mid ='.$id.' and a.mkind=2'








