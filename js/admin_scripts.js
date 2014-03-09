function checkreinit(id_popup, form_id) {
    var status = $('#reinit_passwd').is(':checked').valueOf();
    if (status) {
        toggle_popup(id_popup);
    } else {
        document.forms[form_id].submit();
    }
}