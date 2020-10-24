
window.onscroll = function () {

    if (document.documentElement.scrollTop > 50) {
        document.getElementById("homeMenu").style.backgroundColor = "rgba(247, 247, 247)";
        document.getElementById("homeMenu").style.borderBottom = "1px solid rgba(26, 26, 26)";
        document.getElementById("homeMenu").style.boxShadow = "0 .5rem 1rem rgba(0,0,0,.15)";
    } else {
        document.getElementById("homeMenu").style.backgroundColor = "rgba(255, 255, 255, 0)";
        document.getElementById("homeMenu").style.borderBottom = "none";
        document.getElementById("homeMenu").style.boxShadow = "none";
    }
}



