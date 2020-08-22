jQuery(document).ready(function ($) {
    if ($("#md-progress-bar-container")) {
        const pb =
            '<div class="md-progress-bar-container" id="md-progress-bar-container"><div class="md-progress-bar" id="md-progress-bar"></div></div>';
        const navEls = $("nav");
        const firstNav = $("nav:first");
        const firstNavHtml = firstNav.html();
        // console.log(firstNav);
        const firstNavNewHtml = firstNavHtml + pb;
        firstNav.html(firstNavNewHtml);
    }

    let siteHeaderHeight = $("header#site-header").height();
    let progressBarHeight = $(".md-progress-bar-container").height();

    //$("body").css("marginTop", siteHeaderHeight + progressBarHeight);

    $(window).scroll(function () {
        let windowSrollTop = $(window).scrollTop();

        let height =
            document.documentElement.scrollHeight -
            document.documentElement.clientHeight;

        let scrollRatio = (windowSrollTop / height) * 100;

        $("#md-progress-bar").css("width", scrollRatio + "%");
    });
});
