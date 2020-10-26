
$(document).ready(function(){
    $("#page1").parent("li.num-page").addClass('active');

    $('.num-page a').click(function(){
        var pageId = $(this).attr('id');
        var numPage = $(this).html();
        var pathName = window.location.pathname;

        $.ajax({
            url:        pathName,
            type:       "POST",
            data:       'numPage='+numPage,
            dataType:   'json',
            async:       true,
        
            success: function(data,status) {
                $("#commentList").html(data.body);
                $(".num-page").removeClass('active');
                $("#"+pageId).parent('li.num-page').addClass('active');
            },
            error : function(xhr, textStatus, errorThrown) {  
                alert('Demande échouée.');  
            }
        });
    });  
});

