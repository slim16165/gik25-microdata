jQuery(document).ready(function($) {

    var siteHeaderHeight = $('header#site-header').height();
    var progressBarHeight = $('.md-progress-bar-container').height();

    $('body').css('marginTop', siteHeaderHeight + progressBarHeight);

    $(window).scroll(function() {

        var windowSrollTop = $(window).scrollTop();

        var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;

        var scrollRatio = (windowSrollTop / height) * 100;

        $('#md-progress-bar').css('width', scrollRatio + '%');
        
    });

});