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

