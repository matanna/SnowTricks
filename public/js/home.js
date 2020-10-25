
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

    if (document.documentElement.scrollTop > 200) {
        document.getElementById("arrowDown").style.visibility = "hidden";
        document.getElementById("arrowUp").style.visibility = "visible";
    } else {
        document.getElementById("arrowDown").style.visibility = "visible";
        document.getElementById("arrowUp").style.visibility = "hidden"; 
    }
}

$(document).ready(function(){
    $("#moreTricks").on("click", function(event){
        $.ajax({
            url:        '{{ path('/') }}',
            type:       'POST',
            dataType:   'json',
            async:      true,

            success: function(data,status) {
                $('#firstTricks').append(data.body); 
                $('#moreTricksAction').html('<a href="/#firstTricks" id="lessTricks" class="btn btn-outline-primary text-center">Voir moins de tricks</a>');
            },
            error : function(xhr, textStatus, errorThrown) {  
                alert('Demande échouée.');  
            }
        })

    })
    
});

$(document).ready(function(){
    $("#lessTricks").on("click", function(event){
        $(".more-tricks").remove();
    })
});

