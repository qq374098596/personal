<template>
  <div>
    <el-card style="height: 610px;">
      <quill-editor
        v-model="content"
        ref="myQuillEditor"
        style="height: 500px;"
        :options="editorOption">
      </quill-editor>
    </el-card>
    <el-button type="primary" @click="onSubmit" style="margin-top: 20px; float: right;">提交</el-button>
  </div>
</template>

<script>
import {quillEditor} from 'vue-quill-editor'
import 'quill/dist/quill.core.css'
import 'quill/dist/quill.snow.css'
import 'quill/dist/quill.bubble.css'

export default {
  name: 'FuncFormsEdit',
  components: {
    quillEditor
  },
  data () {
    return {
      content: null,
      editorOption: {
      	debug: 'info',
			  placeholder: '请开始布置您的品牌简介...',
			  readOnly: false,
			  theme: 'snow',
			  modules: {
			    toolbar: [
					  [ 'image', 'bold', 'italic', 'underline', 'strike'],        // toggled buttons
					  [{ 'list': 'ordered'}, { 'list': 'bullet' }],
					  [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
					  [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
					  [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
					  [{ 'align': [] }],
					
					  ['clean']                                         // remove formatting button
					]
			  },
      }
    }
  },
  created:function(){
  	console.log(document.body.scrollTop)
    this.$axios.get('api/froms/summary').then(data=>{
      const $data = data.data;
      if($data.s==500){
        alert($data.error)
        this.$router.push('/login')
      }else{
        this.$axios.get('api/froms/summary').then(data=>{
          const $data = data.data;
          this.content = $data.summary.jianjie;
        }).catch(err=>{
    
        })
      }
    }).catch(err=>{
      
    })
  },
  methods:{
  	onSubmit () {

  		this.$axios.post('api/froms/upSummary',{pid:this.user.pid,content:this.content}).then(data=>{
        console.log(data);
      })
  	}
  }
}
</script>

<style scoped>

</style>
