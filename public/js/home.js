
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
            type:       'GET',
            dataType:   'json',
            async:      true,

            success: function(data,status) {
                var data_length = data.length;
                $("#firstTricks").after('<div class="row" id="nextTricks">');
                for(i = 0; i < data_length; i++) {
                    var photo = data[i]['photo'];
                    var name = data[i]['name'];
                    var id = data[i]['id'];
                    var category = data[i]['category'];
                    $("#nextTricks").append(
                                            '<div class="col-xl-3">\
                                                <div class="card m-5 shadow">\
                                                    <img style="height: 200px; width: 100%; display: block;" src="'+photo+'" alt="Snowboard" id="photoTricks">\
                                                    <h4 class="card-header nameTricks">'+name+'</h4>\
                                                    <div class="card-body">\
                                                        <a href="/tricks/'+id+'" class="btn btn-outline-primary btn-sm">Voir le tricks</a>\
                                                    </div>\
                                                    <div class="card-footer text-muted">\
                                                        <p>Style : <span class="badge badge-primary">'+category+'</span></p>\
                                                        <p>Crée le {{ tricks.dateAtCreated | date("m/d/Y") }}<br />\
                                                           Ajouté par {{ tricks.user.fullname }}</p>\
                                                    </div>'
                    );
                }  
            },

            error : function(xhr, textStatus, errorThrown) {  
                alert('Demande échouée.');  
            }
        })
    })
});
