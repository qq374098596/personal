webpackJsonp([11],{"bNA/":function(e,t){},qkHe:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=a("vMJZ"),l={name:"Reply",data:function(){return{tableData:[],currentPage:1,pageSize:10,pageTotal:0,dialogFormVisible:!1,form:{name:"",date:"",content:"不知道说什么，给您拜个早年吧",reply:"",index:0}}},created:function(){this.$axios.get("http://adminc.ah123.top/usermessages/advice").then(function(e){console.log(e)}).catch(function(e){})},mounted:function(){},methods:{handleEdit:function(e,t){this.form.index=e+(this.currentPage-1)*this.pageSize,this.form.name=t.name,this.form.date=t.date,this.form.address=t.address,this.dialogFormVisible=!0},handleSizeChange:function(e){this.pageSize=e},handleCurrentChange:function(e){this.currentPage=e},ReplyClick:function(){this.dialogFormVisible=!1,this.$message({message:"回复"+this.form.name+"成功！",type:"success"})},downExcel:function(){var e=["date","name","address"],t=this.tableData.map(function(t){return e.map(function(e){return t[e]})});this.$toExcel({th:["时间","用户名","地址"],data:t,fileName:"问答",fileType:"xlsx",sheetName:"问答页"})}},watch:{pageSize:function(e,t){var a=this;Object(n.b)(this.formInline).then(function(e){a.pageTotal=e.data.length,a.tableData=e.data})}}},o={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("el-card",[a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.tableData.slice((e.currentPage-1)*e.pageSize,e.currentPage*e.pageSize),"empty-text":"暂无推送"}},[a("el-table-column",{attrs:{label:"用户名",width:"180"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-popover",{attrs:{trigger:"hover",placement:"top"}},[a("p",[e._v("用户名: "+e._s(t.row.name))]),e._v(" "),a("p",[e._v("已加盟")]),e._v(" "),a("div",{staticClass:"name-wrapper",attrs:{slot:"reference"},slot:"reference"},[a("el-tag",{attrs:{size:"medium"}},[e._v(e._s(t.row.name))])],1)])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"联系方式"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("i",{staticClass:"el-icon-mobile-phone"}),e._v(" "),a("span",{staticStyle:{"margin-left":"10px"}},[e._v(e._s(t.row.date))])]}}])}),e._v(" "),a("el-table-column",{attrs:{label:"操作",width:"100"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{size:"mini"},on:{click:function(a){e.handleEdit(t.$index,t.row)}}},[e._v("查看\n            ")])]}}])})],1),e._v(" "),a("el-pagination",{attrs:{background:"","current-page":e.currentPage,"page-sizes":[10,20,30,40],"page-size":e.pageSize,layout:"total, sizes, prev, pager, next, jumper",total:e.pageTotal},on:{"size-change":e.handleSizeChange,"current-change":e.handleCurrentChange}}),e._v(" "),a("el-col",{attrs:{span:12,xs:24}},[a("el-dialog",{attrs:{title:"评论信息",visible:e.dialogFormVisible},on:{"update:visible":function(t){e.dialogFormVisible=t}}},[a("el-form",{attrs:{model:e.form}},[a("el-col",{attrs:{span:11}},[a("el-form-item",{attrs:{label:"用户名","label-width":"120px"}},[a("el-input",{attrs:{"auto-complete":"off",readonly:""},model:{value:e.form.name,callback:function(t){e.$set(e.form,"name",t)},expression:"form.name"}})],1)],1),e._v(" "),a("el-col",{attrs:{span:11}},[a("el-form-item",{attrs:{label:"咨询时间","label-width":"120px"}},[a("el-input",{attrs:{"auto-complete":"off",readonly:""},model:{value:e.form.date,callback:function(t){e.$set(e.form,"date",t)},expression:"form.date"}})],1)],1),e._v(" "),a("el-col",{attrs:{span:24}},[a("el-form-item",{attrs:{label:"咨询内容","label-width":"120px"}},[a("el-input",{attrs:{"auto-complete":"off",readonly:""},model:{value:e.form.content,callback:function(t){e.$set(e.form,"content",t)},expression:"form.content"}})],1)],1),e._v(" "),a("el-col",{attrs:{span:24}},[a("el-form-item",{attrs:{label:"回复内容","label-width":"120px"}},[a("el-input",{attrs:{"auto-complete":"off"},model:{value:e.form.reply,callback:function(t){e.$set(e.form,"reply",t)},expression:"form.reply"}})],1)],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{on:{click:function(t){e.dialogFormVisible=!1}}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{type:"primary"},on:{click:function(t){e.ReplyClick()}}},[e._v("确 定")])],1)],1)],1)],1)],1)},staticRenderFns:[]};var i=a("VU/8")(l,o,!1,function(e){a("bNA/")},null,null);t.default=i.exports}});