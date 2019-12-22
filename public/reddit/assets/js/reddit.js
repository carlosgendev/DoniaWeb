function Reddit(){
    var self= this;
    var _current_link = "";
    var _current_link_photos = [];
    var _started = false;
    var _module_name = "reddit";
    this.init= function(){
        if($("."+_module_name+"-app").length > 0){
            self.optionReddit();
            self.loadPreview();
            self.loadPreviewPostAll();
        }
    };

    this.optionReddit = function(){
        /*
        * Select type post
        */
        if($("[name='default_type']").val() != undefined){
            _default_type = $("[name='default_type']").val();
            setTimeout(function(){
                _that = $(".schedule-"+_module_name+"-type .item[data-type='"+_default_type+"']");
                self.loadPostForm(_that, false);
                if(_default_type == "link"){
                    $("."+_module_name+"-app input[name='link']").change();
                }
            }, 50);
        }

        //Enable Schedule
        $(document).on("change", ".enable_"+_module_name+"_schedule", function(){
            _that = $(this);
            if(!_that.hasClass("checked")){
                $('.time_post').removeAttr('disabled');
                $('.btnPostNow').addClass("hide");
                $('.btnSchedulePost').removeClass("hide");
                _that.addClass('checked');
            }else{
                $('.time_post').attr('disabled', true);
                $('.btnPostNow').removeClass("hide");
                $('.btnSchedulePost').addClass("hide");
                _that.removeClass('checked');        
            }
            return false;
        });

        //Post now
        $(document).on("click", "."+_module_name+"-app .btnPostNow", function(){
            _that = $(this);
            self.postNow(_that);
        });

        //
        $(document).on("click", ".schedule-"+_module_name+"-type .item", function(){
            self.loadPostForm($(this), true);
        });
    };

    this.postNow = function(_that){
        _form    = _that.closest("form");
        _action  = _form.attr("action");
        _data    = $("[name!='account[]']").serialize();
        _data    = _data + '&' + $.param({token:token});
        _item    = $(".list-account .item.active");
        _stop    = false;
        if(_item.length > 0){
            _id     = _item.first().find("input").val();
            _data   = _form.serialize();
            _data   = Main.removeParam("account%5B%5D", _data);
            _data   = _data + '&' + $.param({token:token , 'account[]' :_id});
        }else{
            if(_started == true){
                _started = false;
                Main.statusCardOverplay("hide");
                return false;
            }
        }

        Main.statusOverplay("hide");
        Main.statusCardOverplay("show");

        Main.ajax_post(_that, _action, _data, function(_result){
            Main.statusOverplay("show");
            _started = true;

            //Remove mark item
            if(_result.stop == undefined){
                _item.first().trigger("click");
                setTimeout(function(){
                    $(".btnPostNow").trigger("click");
                }, 500);
            }else{
                Main.statusCardOverplay("hide");
            }
        });
    };

    this.loadPostForm = function(_that, _load_default_preview){
        _type = _that.data("type");
        _that.addClass("active");
        _that.siblings().removeClass("active");
        _that.siblings().find("input").removeAttr('checked');
        _that.find("input").attr('checked','checked');

        if(_type == "text"){
            $(".image-manage").hide();
            $(".form-caption").removeClass("hide");
        }

        if(_type == "link"){
            $(".image-manage").hide();
            $(".form-caption").addClass("hide");
        }

        if(_type == "media"){
            $(".image-manage").show();
            $(".form-caption").addClass("hide");
        }

        if(_type != "link"){
            $("#link").removeClass("active");
            $("#link input").val("");
        }else{
            $("#link").addClass("active");
        }

        $(".preview-"+_module_name+"-view").addClass("hide");
        $(".preview-"+_module_name+"-"+_type).removeClass("hide");

        if(_load_default_preview){
            self.defaultPreview();
        }
    };

    this.loadPreview = function(){

        //Review title
        _title = $("."+_module_name+"-app input[name='title']").val();
        if(_title != ""){
            $(".preview-title").html(_title);
        }
        $(document).on("change keyup", "."+_module_name+"-app input[name='title']", function(){
            _data = $(this).val();
            if(_data != ""){
                $(".preview-title").html(_data);
            }else{
                $(".preview-title").html('<div class="line-no-text">');
            }
        });

        //Review title
        _link = $("."+_module_name+"-app input[name='link']").val();
        if(_link != "" && _link != undefined){
            _link = _link.replace("https://", "");
            _link = _link.replace("http://", "");
            if(_link.length > 20){
                _link = _link.substring(0, 20)+"...";
            }
            $(".preview-link").html(_link+' <i class="fa fa-external-link open" aria-hidden="true"></i>');
        }
        $(document).on("change keyup", "."+_module_name+"-app input[name='link']", function(){
            _data = $(this).val();
            
            if(_data != "" && _data != undefined){
                _data = _data.replace("https://", "");
                _data = _data.replace("http://", "");
                if(_data.length > 20){
                    _data = _data.substring(0, 20)+"...";
                }


                $(".preview-link").html(_data+' <i class="fa fa-external-link open" aria-hidden="true"></i>');
            }else{
                $(".preview-link").html('<div class="line-no-text">');
            }
        });

        //Review content
        if($(".post-message").length > 0){
            if($(".all-post .add_link").length > 0){
                $(".post-message").data("emojioneArea").on("keyup", function(editor) {
                    _data = editor.html();
                    if(_data != ""){
                        $(".preview-title").html(_data);
                    }else{
                        $(".preview-title").html('<div class="line-no-text"></div>');
                    }
                });

                $(".post-message").data("emojioneArea").on("change", function(editor) {
                    _data = editor.html();
                    if(_data != ""){
                        $(".preview-title").html(_data);
                    }else{
                        $(".preview-title").html('<div class="line-no-text"></div>');
                    }
                });

                $(".post-message").data("emojioneArea").on("emojibtn.click", function(editor) {
                    _data = $(".emojionearea-editor").html();
                    if(_data != ""){
                        $(".preview-title").html(_data);
                    }else{
                        $(".preview-title").html('<div class="line-no-text"></div>');
                    }
                });
            }else{
                $(".post-message").data("emojioneArea").on("keyup", function(editor) {
                    _data = editor.html();
                    if(_data != ""){
                        $(".preview-reddit-caption").html(_data);
                    }else{
                        $(".preview-reddit-caption").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                    }
                });

                $(".post-message").data("emojioneArea").on("change", function(editor) {
                    _data = editor.html();
                    if(_data != ""){
                        $(".preview-reddit-caption").html(_data);
                    }else{
                        $(".preview-reddit-caption").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                    }
                });

                $(".post-message").data("emojioneArea").on("emojibtn.click", function(editor) {
                    _data = $(".emojionearea-editor").html();
                    if(_data != ""){
                        $(".preview-reddit-caption").html(_data);
                    }else{
                        $(".preview-reddit-caption").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                    }
                });
            }
        }

        //Load Preview
        _video_tmp = "";
        setInterval(function(){ 
            _type  = $(".schedule-"+_module_name+"-type .item.active").data("type");
            _type = ( _type == undefined )?$(".all-post .list-action .active input").val():_type;
            _media = $(".file-manager-list-images .item");

            if(_media.length > 0){
                _first_media = _media.first();
                _link = _first_media.find("input").val();
                _link_parse = _link.split(".");
                _mime = _link_parse[1].toLowerCase();

                switch(_type){
                    case "media":
                        if(_mime != "mp4"){
                            $(".preview-"+_module_name+" .preview-"+_module_name+"-media .preview-content .preview-image").html('').css({'background-image': 'url(' + _link + ')'});
                        }else{
                            if(_video_tmp != _link){
                                $(".preview-"+_module_name+" .preview-"+_module_name+"-media .preview-content .preview-image").html('<video src="'+_link+'" playsinline="" autoplay="" muted="" loop=""></video>');
                                $(".preview-"+_module_name+" .preview-"+_module_name+"-media .preview-content .preview-image").css({"background-image": "none"});
                            }
                            _video_tmp = _link;
                        }
                        
                        break;

                    case "photo":
                        $(".preview-"+_module_name+" .preview-"+_module_name+"-media .preview-content .preview-image").css({'background-image': 'url(' + _link + ')'});
                        break;

                    case "video":
                        if(_video_tmp != _link){
                            $(".preview-"+_module_name+" .preview-"+_module_name+"-media .preview-content .preview-image").html('<video src="'+_link+'" playsinline="" autoplay="" muted="" loop=""></video>');
                            $(".preview-"+_module_name+" .preview-"+_module_name+"-media .preview-content .preview-image").css({"background-image": "none"});
                        }
                        _video_tmp = _link;
                        break;
                }
            }
        }, 1500);
    };

    this.loadPreviewPostAll = function(){
        $(document).on("click", ".all-post .list-action li a", function(){
            _that = $(this);
            _li = _that.parents("li");
            _id = _that.attr("href");
            _type = _id.replace("#", "");
            switch(_type){
                case "text":
                    $(".preview-"+_module_name+"-view").addClass("hide");
                    $(".preview-"+_module_name+"-"+_type).removeClass("hide");
                    self.defaultPreview();
                    break;

                case "link":
                    $(".preview-"+_module_name+"-view").addClass("hide");
                    $(".preview-"+_module_name+"-"+_type).removeClass("hide");
                    self.defaultPreview();
                    break;

                case "photo":
                    _type = "media";
                    $(".preview-"+_module_name+"-view").addClass("hide");
                    $(".preview-"+_module_name+"-"+_type).removeClass("hide");
                    self.defaultPreview();
                    break;

                case "video":
                    $(".preview-"+_module_name+"-view").addClass("hide");
                    $(".preview-"+_module_name+"-none").removeClass("hide");
                    self.defaultPreview();
                    break;
            }
        });

        setInterval(function(){ 
            if($(".all-post .add_link").length > 0){
                _link = $(".all-post .add_link").val();
                if(_link != undefined && _link != ""){
                    _link = _link.replace("https://", "");
                    _link = _link.replace("http://", "");
                    if(_link.length > 20){
                        _link = _link.substring(0, 20)+"...";
                    }
                    $("."+_module_name+"-app .preview-"+_module_name+"-link .preview-link").html(_link);
                }
            }
        }, 1500);
    };

    this.defaultPreview = function(){
        $(".preview-"+_module_name+" .preview-"+_module_name+"-link .preview-image").attr("style", "").html('');
        $(".preview-"+_module_name+" .preview-"+_module_name+"-media .image").attr("style", "").html('');
        $(".preview-"+_module_name+" .preview-"+_module_name+"-link .title").html('<div class="line-no-text"></div>');
        $(".preview-"+_module_name+" .preview-"+_module_name+"-link .website").html('<div class="line-no-text w50"></div>');
        $(".preview-"+_module_name+" .preview-"+_module_name+"-media .preview-content .preview-image").html('');
    };
}

Reddit= new Reddit();
$(function(){
    Reddit.init();
});
