/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


var storeTimestamp = null;
var storeTimeFormat24 = true;
var youtubeImageUrl = null;

pjQuery_1_10_2(document).ready(function() {	
	
    /* Mode */
    var currentMode = null;
    var _modeSync = function()
    {
        pjQuery_1_10_2('.plcs-block-mode').removeClass('plcs-enabled');
        var mode = pjQuery_1_10_2('input[type=radio][name=comingsoon_mode]:checked').val();
        if(!currentMode) {
            currentMode = mode? mode : 'none';
        }
        if(mode) {
            var $block = pjQuery_1_10_2('.plcs-block-mode[data-mode='+ mode +']').addClass('plcs-enabled');
            if(mode == currentMode) {
                $block.find('.plcs-status-enabled').show();
                $block.find('.plcs-status-save').hide();
            }else{
                $block.find('.plcs-status-enabled').hide();
                $block.find('.plcs-status-save').show();
            }
        }
    }

    pjQuery_1_10_2('.plcs-b-link').on('click', function() {
        var $block = pjQuery_1_10_2(this).parents('.plcs-block-mode');
        pjQuery_1_10_2('input[type=radio][value='+ $block.data('mode') +']').prop('checked', true);
        $block.find('.plcs-icon-block').removeClass('plcs-hover');
        _modeSync();
    });
    _modeSync();

    pjQuery_1_10_2('.plcs-icon-block').on('mouseenter', function() {
        pjQuery_1_10_2(this).addClass('plcs-hover');
    })
    .on('mouseleave', function() {
        pjQuery_1_10_2(this).removeClass('plcs-hover');
    });
    
    // Do preview.
    pjQuery_1_10_2('.entry-edit .plcs-preview').on('click', function () {
        var $this = pjQuery_1_10_2(this);
        var href = '';
        if($this.hasClass('plcs-preview-live')) {
            href = modLivePath;
        }else if($this.hasClass('plcs-preview-comingsoon')) {
            href = modComingsoonPath;
        }else if($this.hasClass('plcs-preview-maintenance')) {
            href = modMaintenancePath;
        }

        if(href) {
            window.open(href);
        }
        return false;
    });

    pjQuery_1_10_2('#edit_tabs a').on('click', function() {
        pjQuery_1_10_2('.content-header .plcs-preview').hide();
        if(this.id == 'edit_tabs_comingsoon_section') {
            pjQuery_1_10_2('.content-header .plcs-preview-comingsoon').show();
            pjQuery_1_10_2('#comingsoon_uploader').after(pjQuery_1_10_2('.flex'), pjQuery_1_10_2('.uploader'));
        }else if(this.id == 'edit_tabs_maintenance_section') {
            pjQuery_1_10_2('.content-header .plcs-preview-maintenance').show();
            pjQuery_1_10_2('#maintenance_uploader').after(pjQuery_1_10_2('.flex'), pjQuery_1_10_2('.uploader'));
        }
    });

    pjQuery_1_10_2('#edit_tabs a:first').click();

    /* ComingSoon */
    // Fields.
    var _checkEnable = function() {
        var $chk = pjQuery_1_10_2(this);
        if(!$chk.is(':checked')) {
            $chk.parent().parent().addClass('not-active');
        }else{
            $chk.parent().parent().removeClass('not-active');
        }
    }

    // pjQuery_1_10_2('.form-list .grid table.data tbody input.checkbox').click(_checkEnable).each(_checkEnable);
    pjQuery_1_10_2('#comingsoon_signup_fields table.data tbody input.checkbox').click(_checkEnable).each(_checkEnable);

    // Image.
    var $comingsoonImage = pjQuery_1_10_2('#comingsoon_background_image table tbody');
    var comingsoonImageTemplate = $comingsoonImage.find('tr:first-child').eq(0).html();
    $comingsoonImage.find('tr:first-child').remove();

    // Video.
    var $comingsoonVideo = pjQuery_1_10_2('#comingsoon_background_video table tbody');
    var comingsoonVideoTemplate = $comingsoonVideo.find('tr:first-child').eq(0).html();
    $comingsoonVideo.find('tr:first-child').remove();

    pjQuery_1_10_2('#comingsoon_background_video_add_button').on('click', function() {
        var name = 'video-' + Date.now();
        var template = comingsoonVideoTemplate.split('_TMPNAME_').join(name);
        $comingsoonVideo.append('<tr>'+ template +'</tr>');
        return false;
    });

    /* Maintenance */
    // Image.
    var $maintenanceImage = pjQuery_1_10_2('#maintenance_background_image table tbody');
    var maintenanceImageTemplate = $maintenanceImage.find('tr:first-child').eq(0).html();
    $maintenanceImage.find('tr:first-child').remove();

    // ..uploader
    var uploaderVarName = (pjQuery_1_10_2('#comingsoon_background_fieldset .uploader').attr('id')) + 'JsObject';
    var uploader = window[uploaderVarName];
    uploader.onFilesComplete = function(completedFiles)
    {
        completedFiles.each(function(file)
        {
            var response = pjQuery_1_10_2.parseJSON(file.response);
            
            if(pjQuery_1_10_2('#edit_tabs #edit_tabs_comingsoon_section').hasClass('active')) {
                var template = comingsoonImageTemplate.split('_TMPNAME_').join(response.file);
                $comingsoonImage.append('<tr>'+ template +'</tr>');
            }else if(pjQuery_1_10_2('#edit_tabs #edit_tabs_maintenance_section').hasClass('active')) {
                var template = maintenanceImageTemplate.split('_TMPNAME_').join(response.file);
                $maintenanceImage.append('<tr>'+ template +'</tr>');
            }
            
            console.log(response.file);
            console.log(file);

            uploader.removeFile(file.id);
        });
        MediabrowserInstance.handleUploadComplete();
    }

    // Video.
    var $maintenanceVideo = pjQuery_1_10_2('#maintenance_background_video table tbody');
    var maintenanceVideoTemplate = $maintenanceVideo.find('tr:first-child').eq(0).html();
    $maintenanceVideo.find('tr:first-child').remove();

    pjQuery_1_10_2('#maintenance_background_video_add_button').on('click', function() {
        var name = 'video-' + Date.now();
        var template = maintenanceVideoTemplate.split('_TMPNAME_').join(name);
        $maintenanceVideo.append('<tr>'+ template +'</tr>');
        return false;
    });

    var getYoutubeId = function (url)
    {
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = url.match(regExp);
        if (match && match[2] && match[2].length == 11) {
            return match[2];
        }
    }

    var getVimeoId = function (url)
    {
        var regExp = /https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
        var match = url.match(regExp);
        if (match && match[3]) {
            return match[3];
        }
    }

    pjQuery_1_10_2('#comingsoon_background_video, #maintenance_background_video').on('change', 'input.plcs-video-url', function() {
        var $this = pjQuery_1_10_2(this);
        var $image = $this.parent().parent().find('img.plcs-admin-video-image');
        var $video = $this.parent().parent().find('video.plcs-admin-video');
        var url = $this.val();
        if(youtubeId = getYoutubeId(url)) {
            $image.attr('src', youtubeImageUrl.replace('_VIDEO_ID_', youtubeId)).show();
            $video.hide();
        }else if(vimeoId = getVimeoId(url)) {
            pjQuery_1_10_2.get('http://vimeo.com/api/v2/video/' + vimeoId + '.json', function (data) {
                var vimeoImageUrl = data[0].thumbnail_medium;
                $image.attr('src', vimeoImageUrl).show();
            }, 'json')
            .always(function() {
                $video.hide();
            });
        }else if(url){
            $image.hide();
            $video.show();
            $video.find('source').attr('src', url);
            $video.load();
        }else{
            $image.hide();
            $video.hide();
        }
    });

    // Clock.
    function pad(num) {
        return (num < 10 ? '0' : '') + num;
    }

    var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var offset = 0;
    setInterval(function() {
        var date = new Date(storeTimestamp * 1000 + offset * 1000);
        var dateStr = pad(monthNames[date.getUTCMonth()]) +' '+ pad(date.getUTCDate()) +', '+ pad(date.getUTCFullYear()) +' ';
        if(storeTimeFormat24) {
            dateStr += pad(date.getUTCHours()) +':'+ pad(date.getUTCMinutes()) +':'+ pad(date.getUTCSeconds());
        }else{
            var hours = date.getUTCHours();
            hours = hours % 12;
            hours = hours ? hours : 12;
            dateStr += pad(hours) +':'+ pad(date.getUTCMinutes()) +':'+ pad(date.getUTCSeconds()) +' '+ (date.getUTCHours() >= 12 ? 'PM' : 'AM');
        }
        pjQuery_1_10_2('.plcs-current-time span').html(dateStr);
        offset++;
    }, 1000);

    var offsetTopCeil = pjQuery_1_10_2('#edit_tabs').offset().top + pjQuery_1_10_2('#edit_tabs').height();
    pjQuery_1_10_2(window).scroll(function() {
        if(pjQuery_1_10_2(window).scrollTop() > offsetTopCeil - pjQuery_1_10_2('.content-header-floating').height()) {
            pjQuery_1_10_2('.plcs-current-time').addClass('fixed');
        }else{
            pjQuery_1_10_2('.plcs-current-time').removeClass('fixed');
        }
    });
    pjQuery_1_10_2(window).scroll();

    // Inherit.
    pjQuery_1_10_2('#comingsoon_mode_inherit').on('change', function() {
        pjQuery_1_10_2('#row_comingsoon_mode_checker').toggleClass('filter-gray', pjQuery_1_10_2('#comingsoon_mode_inherit').is(':checked'));
    });

    /*var comingsoonSignupFieldsInherit = pjQuery_1_10_2('#comingsoon_signup_fields_inherit')[0];
    toggleValueElements(comingsoonSignupFieldsInherit, Element.previous(comingsoonSignupFieldsInherit.parentNode));*/

    var galleriesInherit = [
        '#comingsoon_background_image_inherit',
        '#comingsoon_background_video_inherit',
        '#maintenance_background_image_inherit',
        '#maintenance_background_video_inherit'
    ];
    pjQuery_1_10_2(galleriesInherit.join(',')).on('change', function() {
        var $this = pjQuery_1_10_2(this);
        var galleryId = $this.attr('id').replace('_inherit', '');
        var $gallery = pjQuery_1_10_2('#'+ galleryId);

        if($this.is(':checked')) {
            $gallery.addClass('filter-gray');
            $gallery.find('img.plcs-gallery-image-preview[src!=""]').show();
            if(galleryId == 'comingsoon_background_image') {
                pjQuery_1_10_2('#row_comingsoon_uploader').hide();
            }else if(galleryId == 'maintenance_background_image') {
                pjQuery_1_10_2('#row_maintenance_uploader').hide();
            }else{
                pjQuery_1_10_2('#row_'+ galleryId +'_add').hide();
            }
        }else{
            $gallery.removeClass('filter-gray');
            $gallery.find('img.plcs-gallery-image-preview[src=""]').hide();
            if(galleryId == 'comingsoon_background_image') {
                pjQuery_1_10_2('#row_comingsoon_uploader').show();
            }else if(galleryId == 'maintenance_background_image') {
                pjQuery_1_10_2('#row_maintenance_uploader').show();
            }else{
                pjQuery_1_10_2('#row_'+ galleryId +'_add').show();
            }
        }
    });

    pjQuery_1_10_2('.config-inherit:checked').click().click();

    /*pjQuery_1_10_2('.value .buttons-set').each(function(i, el) {
        var $this = pjQuery_1_10_2(el);
        var scopeId = '#'+ (el.id.replace('buttons', '')) +'_inherit';

        if(pjQuery_1_10_2(scopeId).is(':checked')) {
            // $this.find('button.show-hide').click();
            $this.parents('tr').addClass('filter-gray');
        }
    });*/
});