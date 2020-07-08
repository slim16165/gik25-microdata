//window.onload = init;

function init() {
    //alert('js-for-enabled-shortcodes.js');
    var level1Headings = document.getElementsByTagName('h1');
    for ( var i = 0; i < level1Headings.length; i++) {
        level1Headings[i].style.color = 'green';
    }
}