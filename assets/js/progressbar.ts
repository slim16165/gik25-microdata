/**
 * Must have the @types/jquery typescript installed
 * either via PHPStorm -> Javascript -> Libraries
 * or `npm i @types/jquery`
 */

function HandleProgressBar(): void {
    const progressbar: string =
        '<div class="md-progress-bar-container" id="md-progress-bar-container"><div class="md-progress-bar" id="md-progress-bar"></div></div>';
    const firstNav = $("nav:first").parent();
    const firstNavHtml: string = firstNav.html();
    // console.log(firstNav);
    const firstNavNewHtml: string = firstNavHtml + progressbar;
    firstNav.html(firstNavNewHtml);

    // let siteHeaderHeight = $("header#site-header").height();
    // let progressBarHeight = $(".md-progress-bar-container").height();

    //$("body").css("marginTop", siteHeaderHeight + progressBarHeight);

    $(window).scroll(function () {
        let windowSrollTop = $(window).scrollTop();

        let height =
            document.documentElement.scrollHeight -
            document.documentElement.clientHeight;

        let scrollRatio = (windowSrollTop / height) * 100;

        $("#md-progress-bar").css("width", scrollRatio + "%");
    });
}

jQuery(HandleProgressBar);
