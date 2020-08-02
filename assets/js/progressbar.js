jQuery(document).ready(function ($) {
  if ($("#md-progress-bar-container")) {
    // var pb = jQuery.parseHTML(
    //   decodeURI(
    //     '<div class="md-progress-bar-container" id="md-progress-bar-container"><div class="md-progress-bar" id="md-progress-bar"></div></div>'
    //   )
    // );

    var pb =
      '<div class="md-progress-bar-container" id="md-progress-bar-container"><div class="md-progress-bar" id="md-progress-bar"></div></div>';
    // var pb2 = $("<div />")
    //   .attr("id", "md-progress-bar-container")
    //   .addClass("md-progress-bar-container");
    var navEls = $("nav");
    var firstNav = $("nav:first");
    //firstNavEl.hide();
    var firstNavHtml = firstNav.html();
    // console.log(firstNav);
    // console.log(firstNavHtml);
    //console.log(firstNavHtml + pb);
    var firstNavNewHtml = firstNavHtml + pb;
    firstNav.html(firstNavNewHtml);
    // navEls.each(function (i) {
    //   if (i == 0) {
    //     // navEls[i].append(pb);
    //     //navEls[i].append("<h1>test</h1>");
    //     console.log(navEls[i].html());
    //     var firsNavHtml = navEls[i].html();
    //     firsNavHtml = firsNavHtml + pb;
    //     navEls[i].html(firsNavHtml);
    //   }
    // });
    //$("#md-progress-bar-container").remove();
  }

  var siteHeaderHeight = $("header#site-header").height();
  var progressBarHeight = $(".md-progress-bar-container").height();

  $("body").css("marginTop", siteHeaderHeight + progressBarHeight);

  $(window).scroll(function () {
    var windowSrollTop = $(window).scrollTop();

    var height =
      document.documentElement.scrollHeight -
      document.documentElement.clientHeight;

    var scrollRatio = (windowSrollTop / height) * 100;

    $("#md-progress-bar").css("width", scrollRatio + "%");
  });
});
