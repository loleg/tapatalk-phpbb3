/*!
 * jQuery Smart Banner
 * Copyright (c) 2012 Arnold Daniels <arnold@jasny.net>
 * Based on 'jQuery Smart Web App Banner' by Kurt Zenisek @ kzeni.com
 */

var default_byo_ios_app_id      =  307880732;
var default_byo_ios_hd_app_id   =  481579541;
var default_byo_app_name        =  "Tapatalk Forum App";
var default_byo_app_hd_name     =  "Tapatalk HD";
var default_byo_app_desc        =  "App for this forum";
var default_byo_app_icon_url    =  tapatalk_dir_url+"/smartbanner/tapatalk2.png";
var default_app_ios_url         =  "http://itunes.apple.com/us/app/tapatalk-forum-app/id307880732?mt=8";
var default_app_ios_hd_url      =  "http://itunes.apple.com/us/app/tapatalk-hd-for-ipad/id481579541?mt=8";
var default_app_android_url     =  "market://details?id=com.quoord.tapatalkpro.activity";
var default_app_android_hd_url  =  "market://details?id=com.quoord.tapatalkHD";
var default_app_kindle_url      =  "http://www.amazon.com/gp/mas/dl/android?p=com.quoord.tapatalkpro.activity";
var default_app_kindle_hd_url   =  "http://www.amazon.com/gp/mas/dl/android?p=com.quoord.tapatalkHD";

if ( typeof(app_ios_msg)        === null ) app_ios_msg        = 'This forum has an app for iPhone and iPod Touch! Click OK to learn more about Tapatalk.';
if ( typeof(app_ios_hd_msg)     === null ) app_ios_hd_msg     = 'This forum has an app for iPad! Click OK to learn more about Tapatalk.';
if ( typeof(app_android_msg)    === null ) app_android_msg    = 'This forum has an app for Android! Click OK to learn more about Tapatalk.';
if ( typeof(app_android_hd_msg) === null ) app_android_hd_msg = 'This forum has an app for Android HD! Click OK to learn more about Tapatalk.';
if ( typeof(app_kindle_msg)     === null ) app_kindle_msg     = 'This forum has an app for Kindle Fire! Click OK to learn more about Tapatalk.';
if ( typeof(app_kindle_hd_msg)  === null ) app_kindle_hd_msg  = 'This forum has an app for Kindle Fire HD! Click OK to learn more about Tapatalk.';

// Support native iOS Smartbanner
if (navigator.userAgent.match(/Safari/i) != null &&
    (navigator.userAgent.match(/CriOS/i) == null && window.Number(navigator.userAgent.substr(navigator.userAgent.indexOf('OS ') + 3, 3).replace('_', '.')) >= 6))
{
    app_location_url = "tapatalk://";
    if (navigator.userAgent.match(/iPad/i) != null)
    {
        app_ipad_id = byo_ios_app_id > 0 ? byo_ios_app_id : default_byo_ios_hd_app_id;
        document.write('<meta name="apple-itunes-app" content="app-id='+app_ipad_id+',app-argument="'+app_location_url+'">');
    }
    else if (navigator.userAgent.match(/iPod|iPhone/i) != null)
    {
        app_iphone_id = byo_ios_app_id > 0 ? byo_ios_app_id : default_byo_ios_app_id;
        document.write('<meta name="apple-itunes-app" content="app-id='+app_iphone_id+',app-argument='+app_location_url+'">');
    }
}


function tapatalkDetect()
{
    // work only when browser support cookie
    if (!navigator.cookieEnabled) return
    
    if (typeof jQuery == "undefined" || navigator.userAgent.match(/Silk/i)) {
        showTapatalkAlert();
    }
    else if (navigator.userAgent.match(/Android/i)) {
        if (navigator.userAgent.match(/mobile/i)) {
            $.smartbanner({
                title: byo_app_name ? byo_app_name : default_byo_app_name,
                author: byo_app_desc ? byo_app_desc : default_byo_app_desc,
                urlOpen: app_location_url,
                urlStore: app_android_url ? app_android_url : default_app_android_url,
                icon: byo_app_icon_url ? byo_app_icon_url : default_byo_app_icon_url,
                force: 'android'
            })
        }
        else {
            $.smartbanner({
                title: byo_app_name ? byo_app_name : default_byo_app_hd_name,
                author: byo_app_desc ? byo_app_desc : default_byo_app_desc,
                urlOpen: app_location_url,
                urlStore: app_android_hd_url ? app_android_hd_url : default_app_android_hd_url,
                icon: byo_app_icon_url ? byo_app_icon_url : default_byo_app_icon_url,
                force: 'android'
            })
        }
        
        if (navigator.userAgent.match(/Android (3|4)/i) && screen.width >=720) {
            $('#smartbanner').css('position', 'fixed')
        }
    }
    else {
        showTapatalkAlert();
    }
}

// tapatalk alert js
function detectTapatalk()
{
    if (navigator.userAgent.match(/iPhone|iPod/i)) {
        tapatalk_alert_message = app_ios_msg;
        tapatalk_alert_url = app_ios_url ? app_ios_url : default_app_ios_url;
    }
    else if (navigator.userAgent.match(/iPad/i)) {
        tapatalk_alert_message = app_ios_hd_msg;
        tapatalk_alert_url = app_ios_hd_url ? app_ios_hd_url : default_app_ios_hd_url;
    }
    else if (navigator.userAgent.match(/Silk/)) {
        if (navigator.userAgent.match(/Android 2/i)) {
            tapatalk_alert_message = app_kindle_msg;
            tapatalk_alert_url = app_kindle_url ? app_kindle_url : default_app_kindle_url;
        }
        else if (navigator.userAgent.match(/Android 4/i)) {
            tapatalk_alert_message = app_kindle_hd_msg;
            tapatalk_alert_url = app_kindle_hd_url ? app_kindle_hd_url : default_app_kindle_hd_url;
        }
    }
    else if (navigator.userAgent.match(/Android/i)) {
        if(navigator.userAgent.match(/mobile/i)) {
            tapatalk_alert_message = app_android_msg;
            tapatalk_alert_url = app_android_url ? app_android_url : default_app_android_url;
        }
        else {
            tapatalk_alert_message = app_android_hd_msg;
            tapatalk_alert_url = app_android_hd_url ? app_android_hd_url : default_app_android_hd_url;
        }
    }
    else if (navigator.userAgent.match(/BlackBerry/i)) {
        tapatalk_alert_message = "This forum has an app for BlackBerry! Click OK to learn more about Tapatalk.";
        tapatalk_alert_url = "http://appworld.blackberry.com/webstore/content/46654?lang=en";
    }

    if (typeof tapatalk_alert_message !== 'undefined' && tapatalk_alert_message && confirm(tapatalk_alert_message))
        window.location = tapatalk_alert_url;
}

function setTapatalkCookies(name, value, exdays)
{
    var exdate = new Date();
    exdate.setDate(exdate.getDate()+exdays);
    value=escape(value)+((exdays==null)?'':'; expires='+exdate.toUTCString());
    document.cookie=name+'='+value+'; path=/;';
}

function showTapatalkAlert()
{
    if (document.cookie.indexOf("tapatalk_redirect=false") < 0)
    {
        detectTapatalk();
        setTapatalkCookies('tapatalk_redirect', 'false', 90);
    }
}

if (typeof jQuery !== "undefined") {
!function($) {
    var SmartBanner = function(options) {
        this.origHtmlMargin = parseFloat($('html').css('margin-top')) // Get the original margin-top of the HTML element so we can take that into account
        this.options = $.extend({}, $.smartbanner.defaults, options)
        
        var standalone = navigator.standalone // Check if it's already a standalone web app or running within a webui view of an app (not mobile safari)

        // Detect banner type (iOS or Android)
        if (this.options.force) {
            this.type = this.options.force
        } else if (navigator.userAgent.match(/iPad|iPhone|iPod/i) != null) {
            if (navigator.userAgent.match(/Safari/i) != null &&
               (navigator.userAgent.match(/CriOS/i) != null ||
               window.Number(navigator.userAgent.substr(navigator.userAgent.indexOf('OS ') + 3, 3).replace('_', '.')) < 6)) this.type = 'ios' // Check webview and native smart banner support (iOS 6+)
        } else if (navigator.userAgent.match(/Android/i) != null) {
            this.type = 'android'
        }
        
        // Don't show banner if device isn't iOS or Android, website is loaded in app or user dismissed banner
        if (!this.type || standalone || this.getCookie('sb-closed') || this.getCookie('sb-installed')) {
            return
        }
        
        // Calculate scale
        this.scale = this.options.scale == 'auto' ? $(window).width() / window.screen.width : this.options.scale
        
        // mobile portrait mode may need bigger scale
        if (navigator.userAgent.match(/mobile/i) && this.scale >= 1 && this.scale < 1.8 && $(window).width() < $(window).height()) {
            if ($(window).width() > 600 || this.scale > 1)
                this.scale = 1.8
        }

        // Get info from meta data
        /*
        var meta = $(this.type=='android' ? 'meta[name="google-play-app"]' : 'meta[name="apple-itunes-app"]')
        if (meta.length == 0) return
        
        this.appId = /app-id=([^\s,]+)/.exec(meta.attr('content'))[1]
        */
        this.title = this.options.title ? this.options.title : $('title').text().replace(/\s*[|\-¡¤].*$/, '')
        this.author = this.options.author ? this.options.author : ($('meta[name="author"]').length ? $('meta[name="author"]').attr('content') : window.location.hostname)
        
        // Create banner
        this.create()
        this.show()
        this.listen()
    }
        
    SmartBanner.prototype = {

        constructor: SmartBanner
    
      , create: function() {
            var iconURL
              , link=(this.options.urlStore ? this.options.urlStore : (this.type=='android' ? 'market://details?id=' : ('https://itunes.apple.com/' + this.options.appStoreLanguage + '/app/id')) + this.appId)
              , linkOpen=this.options.urlOpen ? this.options.urlOpen : "tapatalk://"
              , inStore=this.type=='android' ? this.options.inGooglePlay : this.options.inAppStore
              , gloss=this.options.iconGloss === null ? (this.type=='ios') : this.options.iconGloss

            $('body').append(
                '<div id="smartbanner" class="'+this.type+'">'+
                    '<div class="sb-container">'+
                        '<a href="#" class="sb-close">&times;</a><span class="sb-icon"></span>'+
                        '<div class="sb-info">'+
                            '<strong>'+this.title+'</strong>'+
                            '<span>'+this.author+'</span>'+
                            '<span>'+inStore+'</span>'+
                        '</div>'+
                        '<a href="'+linkOpen+'" class="sb-button-open"><span>'+this.options.buttonOpen+'</span></a>'+
                        '<a href="'+link+'" class="sb-button"><span>'+this.options.buttonStore+'</span></a>'+
                    '</div>'+
                '</div>')
            
            if (this.options.icon) {
                iconURL = this.options.icon
            } else if ($('link[rel="apple-touch-icon-precomposed"]').length > 0) {
                iconURL = $('link[rel="apple-touch-icon-precomposed"]').attr('href')
                if (this.options.iconGloss === null) gloss = false
            } else if ($('link[rel="apple-touch-icon"]').length > 0) {
                iconURL = $('link[rel="apple-touch-icon"]').attr('href')
            }
            if (iconURL) {
                $('#smartbanner .sb-icon').css('background-image','url('+iconURL+')')
                if (gloss) $('#smartbanner .sb-icon').addClass('gloss')
            } else{
                $('#smartbanner').addClass('no-icon')
            }

            this.bannerHeight = $('#smartbanner').outerHeight() - 3

            if (this.scale > 1) {
                $('#smartbanner')
                    .css('top', parseFloat($('#smartbanner').css('top')) * this.scale)
                    .css('height', parseFloat($('#smartbanner').css('height')) * this.scale)
                $('#smartbanner .sb-container')
                    .css('-webkit-transform', 'scale('+this.scale+')')
                    .css('-msie-transform', 'scale('+this.scale+')')
                    .css('-moz-transform', 'scale('+this.scale+')')
                    .css('width', $(window).width() / this.scale)
            }
        }
        
      , listen: function () {
            $('#smartbanner .sb-close').bind('click',$.proxy(this.close, this))
            //$('#smartbanner .sb-button').bind('click',$.proxy(this.install, this))
        }
        
      , show: function(callback) {
            $('#smartbanner').stop().animate({top:0},this.options.speedIn).addClass('shown')
            $('html').animate({marginTop:this.origHtmlMargin+(this.bannerHeight*this.scale)},this.options.speedIn,'swing',callback)
        }
        
      , hide: function(callback) {
            $('#smartbanner').stop().animate({top:-1*this.bannerHeight*this.scale},this.options.speedOut).removeClass('shown')
            $('html').animate({marginTop:this.origHtmlMargin},this.options.speedOut,'swing',callback)
        }
      
      , close: function(e) {
            e.preventDefault()
            this.hide()
            this.setCookie('sb-closed','true',this.options.daysHidden)
        }
       
      , install: function(e) {
            this.hide()
            this.setCookie('sb-installed','true',this.options.daysReminder)
        }
       
      , setCookie: function(name, value, exdays) {
            var exdate = new Date()
            exdate.setDate(exdate.getDate()+exdays)
            value=escape(value)+((exdays==null)?'':'; expires='+exdate.toUTCString())
            document.cookie=name+'='+value+'; path=/;'
        }
        
      , getCookie: function(name) {
            var i,x,y,ARRcookies = document.cookie.split(";")
            for(i=0;i<ARRcookies.length;i++) {
                x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="))
                y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1)
                x = x.replace(/^\s+|\s+$/g,"")
                if (x==name) {
                    return unescape(y)
                }
            }
            return null
        }
      
      // Demo only
      , switchType: function() {
          var that = this
          
          this.hide(function() {
            that.type = that.type=='android' ? 'ios' : 'android'
            var meta = $(that.type=='android' ? 'meta[name="google-play-app"]' : 'meta[name="apple-itunes-app"]').attr('content')
            that.appId = /app-id=([^\s,]+)/.exec(meta)[1]
            
            $('#smartbanner').detach()
            that.create()
            that.show()
          })
        }
    }

    $.smartbanner = function(option) {
        var $window = $(window)
        , data = $window.data('typeahead')
        , options = typeof option == 'object' && option
      if (!data) $window.data('typeahead', (data = new SmartBanner(options)))
      if (typeof option == 'string') data[option]()
    }
    
    // override these globally if you like (they are all optional)
    $.smartbanner.defaults = {
        title: null, // What the title of the app should be in the banner (defaults to <title>)
        author: null, // What the author of the app should be in the banner (defaults to <meta name="author"> or hostname)
        price: 'FREE', // Price of the app
        appStoreLanguage: 'us', // Language code for App Store
        inAppStore: 'On the App Store', // Text of price for iOS
        inGooglePlay: 'In Google Play', // Text of price for Android
        icon: null, // The URL of the icon (defaults to <meta name="apple-touch-icon">)
        iconGloss: null, // Force gloss effect for iOS even for precomposed
        buttonOpen: 'View', // Text for the install button
        buttonStore: 'Install', // Text for the install button
        urlOpen: null, // The URL for the buttonOpen. 
        urlStore: null, // The URL for the buttonStore.
        scale: 'auto', // Scale based on viewport size (set to 1 to disable)
        speedIn: 300, // Show animation speed of the banner
        speedOut: 400, // Close animation speed of the banner
        daysHidden: 15, // Duration to hide the banner after being closed (0 = always show banner)
        daysReminder: 90, // Duration to hide the banner after "VIEW" is clicked *separate from when the close button is clicked* (0 = always show banner)
        force: null // Choose 'ios' or 'android'. Don't do a browser check, just always show this banner
    }
    
    $.smartbanner.Constructor = SmartBanner

}(window.jQuery);
}
