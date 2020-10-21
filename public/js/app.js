
window.onscroll = function () {

    if (document.documentElement.scrollTop > 50) {
        document.getElementById("homeMenu").style.backgroundColor = "rgba(247, 247, 247)";
        document.getElementById("homeMenu").style.borderBottom = "1px solid rgba(26, 26, 26)"
    } else {
        document.getElementById("homeMenu").style.backgroundColor = "rgba(255, 255, 255, 0)";
        document.getElementById("homeMenu").style.borderBottom = "none";
    }
}

