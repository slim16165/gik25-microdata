jQuery(document).ready(function($) {

    $('.mdbb-wrapper').each(function() {
        $(this).hover(
            function() {
                $(this).children('.mdbb').addClass('mdbb-active');
            },
            function() {
                $(this).children('.mdbb').removeClass('mdbb-active');
            },
        );
    });

    function addBBActive() {
        $('.mdbb-wrapper').each(function(i) {
            if(!$(this).is(":hover")) {
                $(this).children('.mdbb').addClass('mdbb-active');
                setTimeout(removeBBActive, 500, i);
            }
        });
    }

    function removeBBActive(j) {
        $('.mdbb-wrapper').each(function(i) {
            if(j == i) {
                $(this).children('.mdbb').removeClass('mdbb-active');
            }
        });
    }

    setInterval(addBBActive, 5000);

});