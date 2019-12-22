<?php
    $module_name = "tumblr";
?>
<div class="<?=$module_name?>-app">

    <div class="preview-<?=$module_name?>">
        <div class="card">
            <div class="card-block p0">

                <!--TEXT-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-text hide">
                    <div class="preview-header">
                        <div class="preview-logo"><i class="fa fa-<?=$module_name?>"></i></div>
                    </div>

                    <div class="preview-content">
                        <div class="preview-user">
                            <div class="text">
                                <div class="name"> <?=lang('anonymous')?></div>
                            </div>
                        </div>

                        <div class="preview-title">
                            <div class="line-no-text"></div>
                        </div>

                        <div class="preview-caption">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>
                    </div>

                    <div class="preview-footer">
                        <img src="<?=BASE."public/tumblr/assets/img/action-icon.png"?>">
                        <div class="clearfix"></div>
                    </div>
                </div>

                <!--IMAGE-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-media">
                    <div class="preview-header">
                        <div class="preview-logo"><i class="fa fa-<?=$module_name?>"></i></div>
                    </div>

                    <div class="preview-content">
                        <div class="preview-user">
                            <div class="text">
                                <div class="name"> <?=lang('anonymous')?></div>
                            </div>
                        </div>

                        <div class="preview-image"></div>
                        
                        <div class="preview-caption">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>
                    </div>

                    <div class="preview-footer">
                        <img src="<?=BASE."public/tumblr/assets/img/action-icon.png"?>">
                        <div class="clearfix"></div>
                    </div>
                </div>

                <!--LINK-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-link hide">
                    <div class="preview-header">
                        <div class="preview-logo"><i class="fa fa-<?=$module_name?>"></i></div>
                    </div>

                    <div class="preview-content">
                        <div class="preview-user">
                            <div class="text">
                                <div class="name"> <?=lang('anonymous')?></div>
                            </div>
                        </div>

                        <div class="preview-image"></div>
                        <div class="preview-link-info">
                            <div class="title"><div class="line-no-text"></div></div>
                            <div class="description"><div class="line-no-text"></div><div class="line-no-text"></div></div>
                            <div class="website"><div class="line-no-text w50"></div></div>
                        </div>
                        
                        <div class="preview-caption">
                            <div class="line-no-text"></div>
                            <div class="line-no-text"></div>
                            <div class="line-no-text w50"></div>
                        </div>
                    </div>

                    <div class="preview-footer">
                        <img src="<?=BASE."public/tumblr/assets/img/action-icon.png"?>">
                        <div class="clearfix"></div>
                    </div>
                </div>

                <!--NONE-->
                <div class="preview-<?=$module_name?>-view preview-<?=$module_name?>-none hide">
                    
                    <div class="preview-error">
                        <?=lang("This social network not support post this type post")?>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>   