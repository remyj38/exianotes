function toggle_popup(id) {
    if ($('#popup_' + id).attr('style') == 'display:none;') {
        $('#popup_' + id).attr('style', 'margin-top: -' + $('#popup_' + id).height()*5/7 + 'px; margin-left: -' + $('#popup_' + id).width()*5/7 + 'px;');
        $('body').attr('id', 'fade');
        $('#page').attr('style', 'display:none;');
    } else {
        $('#page').removeAttr('style');
        $('#popup_' + id).attr('style', 'display:none;');
        $('body').removeAttr('id');
    }
}

function init(){
    $(".disabled").removeAttr('disabled');
}

function filter_datas(id) {
    $('.datas').attr('style', 'display: none');
    if (id == "all") {
        $('.datas').removeAttr('style');
    } else {
        $('[data-id="' + id +'"]').removeAttr('style');
    }
     
}



// Initialisation
init();