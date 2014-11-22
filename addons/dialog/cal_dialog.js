var cal_dialog = false;

function calDialog(element,para) {
    if(cal_dialog === false)
        return false;
    $.get(element.href+para,
        function(data) {
            cal_dialog.dialog({title:element.title}).html(data);
            if(!cal_dialog.dialog("isOpen"))
                cal_dialog.dialog("open");
        },"html");
}

$(function() {
    cal_dialog = $('<div></div>');
    $('body').append(cal_dialog);
    cal_dialog.dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        minHeight: 0,
        width: "auto",
        modal: false,
        dialogClass: "cal-dialog-shadow",
        title: "Event"
    });
});
