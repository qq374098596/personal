/// <reference path="jquery-vsdoc.js" />
/// <reference path="jquery.extend.js" />

(function ($) {
    String.prototype.trim2 = function(c) {
        var str = this,
            whitespace = ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000' + (c || '');
        for (var i = 0,len = str.length; i < len; i++) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(i);
                break;
            }
        }
        for (i = str.length - 1; i >= 0; i--) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(0, i + 1);
                break;
            }
        }
        return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
    }
    String.prototype.format = function() {
        var resultStr = this.toString();
        // 参数为对象
        if(typeof arguments[0] === "object") {
            for(var i in arguments[0]) {
                resultStr = resultStr.replace("{" + i + "}", arguments[0][i]);
            }
        }
        // 多个参数
        else {
            for(var i = 0; i < arguments.length; i ++) {
                resultStr = resultStr.replace("{" + i + "}", arguments[i]);
            }
        }
        return resultStr;
    };
    $.fn.upload = function (opt, callback) {
        var $this = this; def = { width: 100, height: 100, length: 1, url: "", path: "", resize: 0, rewidth: 0, reheight: 0 };
        opt = $.extend(def, opt);

        var temp_box = '<table class="nup-img"><tr></tr></table>';
        var temp_item = '<td id="nup{0}" class="nup-img-item"><i class="nup-img-del"></i><img class="nup-img-img" src="{1}" /><input type="file" accept="image/*" class="nup-img-file" name="filedata" tabindex="-1" /></td>';
        this.upload = function(me, item, img, file, cb) {
            var formData = new FormData();
            formData.append('filedata', file);
            $.ajax({
                type: 'post', url: opt.url, data: formData,
                contentType: false, processData: false, cache: false,
                success: function(d) {
                    if (d.ret == 0) {
                        console.log(img, d.url);
                        var text = me.text().trim2(';');
                        if (!img) text = text + ';' + d.url;
                        else text = text.replace(img, d.url);
                        me.text(text.trim2(';'));
                    }
                    if (cb) cb(d);
                },
                error: function () { if (cb) cb({ ret: 2, msg: '上传失败！' }); }
            });
        };
        this.file2img = function(file, cb) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var base64Img = e.target.result;
                var img = new Image();
                img.src = base64Img;
                img.onload = function() { if (cb) cb(img, img.width, img.height); }
            };
            reader.readAsDataURL(file);
        };
        this.resize = function(img, w, h) {
            var info = { w: w, h: h };
            console.log(info);
            var percent = parseFloat(info.w/info.h);
            var maxwidth = { w: opt.rewidth, h: parseInt(opt.rewidth/percent) };
            var maxheight = { w: parseInt(opt.reheight*percent), h: opt.reheight };
            if (maxwidth.h <= opt.reheight) return maxwidth;
            if (maxheight.w <= opt.rewidth) return maxheight;
            return { w: opt.rewidth, h: opt.reheight };
        };
        this.reimg64 = function(img, nw, nh, ow, oh, cb) {
            var canvas = document.createElement("canvas");
            canvas.width = nw; canvas.height = nh;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, ow, oh, 0, 0, nw, nh);
            var base64 = canvas.toDataURL("image/jpeg", 0.8);
            if (cb) cb(base64, canvas);
        };
        this.toblob = function (dataurl) {
            var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
            while (n--) u8arr[n] = bstr.charCodeAt(n);
            return new Blob([u8arr], { type: mime });
        }
        this.event = function (me, item, img) {
            var file = item.find(".nup-img-file");
            var del = item.find('.nup-img-del');
            var upload = function(file) {
                $this.upload(me, item, img, file, function(d) {
                    if (callback) callback(item, d);
                    if (d.ret == 0 && !img) {
                        img = d.url;
                        del.show();
                    }
                });
            }
            file.change(function() {
                var file = this.files[0];
                var size = file.size;
                console.log(size, opt.resize * 1024, file);
                if (opt.rewidth > 0 && opt.reheight > 0) { //启动压缩
                    if (opt.resize == 0 || (opt.resize > 0 && size > opt.resize * 1024)) {
                        $this.file2img(file, function(img, w, h) {
                            var ns = $this.resize(img, w, h);
                            console.log(ns.w, ns.h, w, h);
                            $this.reimg64(img, ns.w, ns.h, w, h, function(base64, canvas) {
                                var blob = $this.toblob(base64);
                                blob.name = file.name;
                                console.log(blob);
                                upload(blob);
                            });
                        });
                    } else upload(file);
                } else upload(file);
            });
            del.click(function() {
                del.hide();
                item.find('.nup-img-img').attr('src', '');
                var text = me.text().trim2(';');
                text = text.replace(img + ';', '');
                text = text.replace(img, '');
                me.text(text.trim2(';'));
                img = '';
            });
            if (img) del.show();
        };
        this.list = function (v) {
            var l = [], vs = v.split(';');
            for (var i = 0; i < vs.length; i++) if (vs[i].trim().length > 0) l.push(vs[i].trim());
            return l;
        };
        this.init = function () {
            $this.each(function () {
                var me = $(this), list = $this.list(me.text());
                me.before(temp_box);
                var box = me.prev().find('tr');
                for (var i = 0; i < opt.length; i++) {
                    var img = list.length > i ? list[i] : '';
                    var id = Math.random().toString().replace('0.', '');
                    box.append(temp_item.format(id, img ? (opt.path + img) : ''));
                    var item = box.find('#nup' + id);
                    item.css({width: opt.width, height: opt.height});
                    $this.event(me, item, img);
                    if (opt.init) opt.init(item, i, me);
                }
            });
        };
        this.size = function(w, h) {
            var me = $(this);
            var items = me.prev().find('.nup-img-item');
            for (var i = 0; i < opt.length; i++) {
                var item = $(items[i]);
                item.css({width: w, height: h});
                if (opt.init) opt.init(item, i, me);
            }
        }
        $this.init();
        return this;
    };
    //富文本
    $.wEditor = function (opt) {
        /***
         *
         * "errno": 0,
         * data 是一个数组，返回若干图片的线上地址
         * "data": [
         * "图片1地址",
         * "图片2地址",
         * "……"
         * ]
         */
        def = { eid:'', cid:'',imgUrl: "", videoUrl: "",path:'',filename:'file' };
        opt = $.extend(def, opt);
        var E = window.wangEditor;
        var editor = new E(opt.eid);
        var content = $(opt.cid);
        editor.customConfig.uploadImgServer = opt.imgUrl;
        editor.customConfig.uploadVideoServer = opt.videoUrl;
        editor.customConfig.uploadFileName = opt.filename;
        editor.customConfig.onchange = function (html) {
            // 监控变化，同步更新到 textarea
            content.val(html)
        };
        editor.customConfig.uploadImgHooks = {
            // 如果服务器端返回的不是 {errno:0, data: [...]} 这种格式，可使用该配置
            // （但是，服务器端返回的必须是一个 JSON 格式字符串！！！否则会报错）
            customInsert: function (insertImg, result, editor) {
                // 图片上传并返回结果，自定义插入图片的事件（而不是编辑器自动插入图片！！！）
                // insertImg 是插入图片的函数，editor 是编辑器对象，result 是服务器端返回的结果
                // 举例：假如上传图片成功后，服务器端返回的是 {url:'....'} 这种格式，即可这样插入图片：
                var url = result.url
                insertImg(opt.path + url)
                // result 必须是一个 JSON 格式字符串！！！否则报错
            },
            fail: function (xhr, editor, result) {
                // 图片上传并返回结果，但图片插入错误时触发
                // xhr 是 XMLHttpRequst 对象，editor 是编辑器对象，result 是服务器端返回的结果
                console.log(result);
            }
        };
        editor.create();
        content.val(editor.txt.html())
    };
} (jQuery));