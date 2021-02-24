(function () {
    var startingTime = new Date().getTime();
    // Load the script
    var script = document.createElement("SCRIPT");
    script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
    script.type = 'text/javascript';
    document.getElementsByTagName("head")[0].appendChild(script);

    // Poll for jQuery to come into existance
    var checkReady = function (callback) {
        if (window.jQuery) {
            callback(jQuery);
        } else {
            window.setTimeout(function () {
                checkReady(callback);
            }, 20);
        }
    };

    // Start polling...
    checkReady(function (jQuery) {
        jQuery(function () {
            var endingTime = new Date().getTime();
            var tookTime = endingTime - startingTime;
            console.log("jQuery is loaded, after " + tookTime + " milliseconds!");
        });
    });
})();

// function openSppTab(evt, tab) {
//     var i;
//     var x = document.getElementsByClassName("spp_tab");
//     for (i = 0; i < x.length; i++) {
//       x[i].style.display = "none";
//       x[i].className.remove = "spp-tab-active";
//     }
//     document.getElementById(tab).style.display = "block";  
//     document.getElementById(tab).classList.add("spp-tab-active");  
//   }


  function openSppTab(tab, className = "spp_tab", tablink = "tablink", evt = event) {
    var i, x, tablinks;
    x = document.getElementsByClassName(className);
    for (i = 0; i < x.length; i++) {
      x[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName(tablink);
    for (i = 0; i < x.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("spp-tab-active", "");
    }
    document.getElementById(tab).style.display = "block";
    evt.currentTarget.classList.add("spp-tab-active");
  }

