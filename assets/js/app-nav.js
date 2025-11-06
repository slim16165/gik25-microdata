(function(){'use strict';\r
  function init(){\r
    var root=document.querySelector('[data-appnav]');\r
    if(!root) return;\r
    var tabs=[].slice.call(root.querySelectorAll('.td-appnav__tab'));\r
    var sections={};\r
    [].slice.call(root.querySelectorAll('.td-appnav__section')).forEach(function(sec){\r
      var id=sec.id.replace('td-section-','');\r
      sections[id]=sec;\r
    });\r
    tabs.forEach(function(tab){\r
      tab.addEventListener('click',function(){\r
        var target=tab.getAttribute('data-section');\r
        tabs.forEach(function(t){t.classList.remove('is-active');t.setAttribute('aria-selected','false');});\r
        tab.classList.add('is-active');\r
        tab.setAttribute('aria-selected','true');\r
        Object.keys(sections).forEach(function(k){sections[k].classList.remove('is-active');});\r
        if(sections[target]){sections[target].classList.add('is-active');}\r
      });\r
    });\r
  }\r
  if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',init);} else {init();}\r
})();\r


