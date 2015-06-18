function cal_deldb() {
    dialog_multi.dialog({
        title: del_db_title,
        buttons: [{
            text: mozilo_lang["yes"],
            click: function() { $('form[name="deldb"]').trigger('submit'); }
        },{
            text: mozilo_lang["no"],
            click: function() { dialog_multi.dialog("close"); }
    }]});
}

function cal_delevent() {
    dialog_multi.dialog({
        title: del_event_title,
        buttons: [{
            text: mozilo_lang["yes"],
            click: function() { $('form[name="event-form-delete"]').trigger('submit'); }
        },{
            text: mozilo_lang["no"],
            click: function() {
                $('.ev-search-item .js-event-del').each(function(){
                    $('form[name="event-form-delete"] input[name="event['+$(this).val()+']"]').val("false");
                });
                dialog_multi.dialog("close");
            }
    }]});
}

$(function() {

    $('.catpageselect').multiselect({
        multiple: false,
        showClose: false,
        showSelectAll:false,
        closeOptgrouptoggle: false,
        noneSelectedText: false,
        selectedList: 1,
    }).multiselectfilter();

    $('input[type="text"]')
        .filter(':not(input[name="event[CAL_DATE]"])')
        .keydown(function(e){
            if(e.which === 13) e.preventDefault();
            if(e.which === 27) $(this).val("");
    });

    $('.ev-search').mofilterplugin({
        search_item:'.ev-search-item',
        search_name:'.ev-search-name',
        filter_text: cal_filter_text,
        filter_action: "calendar"
    });
/*

    $('form[name="event-form-delete"]').appendTo('.js-mofilterplugin').submit(function(e){
        var no_delete = true;
        $('.ev-search-item:visible .js-event-del:checked').each(function(){
            no_delete = false;
            $('form[name="event-form-delete"] input[name="event['+$(this).val()+']"]').val("true");
        });
        if(no_delete) {
            e.preventDefault();
            dialog_open("error_messages",returnMessage(false, cal_error_del_no_selectet));
        }
    });
*/
    $('form[name="event-form-delete"]').appendTo('.js-mofilterplugin');
    $('button[name="admin_event_delete_button"]').click(function(e){
        e.preventDefault();
        var no_delete = true;
        var content = "";
        $('.ev-search-item:visible .js-event-del:checked').each(function(){
            no_delete = false;
            $('form[name="event-form-delete"] input[name="event['+$(this).val()+']"]').val("true");
            content += '<b>' + $(this).val() + '</b><br />';
        });
        if(no_delete) {
            dialog_open("error_messages",returnMessage(false, cal_error_del_no_selectet));
        } else {
            dialog_open("cal_delevent",content);
        }
    });

    $('form[name="deldb"] input[type="submit"]').click(function(e){
        e.preventDefault();
        dialog_open("cal_deldb",'<b>' + $('form[name="deldb"] input[name="deldb"]').val() + '</b>');
    });

    $('form[name="event-form-delete-old"]').appendTo('.js-mofilterplugin');

    $('#cal-select-all').click(function(){
        if($(this).is(":checked"))
            $('.ev-search-item:visible .js-event-del').prop("checked",true);
        else
            $('.ev-search-item:visible .js-event-del').prop("checked",false);
    });

    $('input[type="search"]').keyup(function(){
        if($.trim($(this).val()))
            $('#cal-select-all').prop("checked",false);
    });

    $('form[name="event-form-new"], .ev-search-item form').submit(function(e){
        var that_input = $(this).find('input[name="event[CAL_DATE]"]');
        if(that_input.val().length < 2) {
            e.preventDefault();
            dialog_open("error_messages",returnMessage(false, cal_error_date_empty));
        }
        if(that_input.hasClass('ca-admin-input-date-error')) {
            e.preventDefault();
            dialog_open("error_messages",returnMessage(false, cal_error_date_exists));
        }
    });

    var cal_search_free = $('.ev-search-name').map( function(v,i) {
            return $(this).text().toLowerCase();
        });

    $.timepicker.setDefaults(mo_date_timepicker);

    $('input[name="event[CAL_DATE]"]').datetimepicker({
        showWeek: true,
        dateFormat: "yy-mm-dd",
        timeFormat: 'HH-mm',
        separator: '-',
        showButtonPanel: false,
        stepMinute:5,
        changeMonth: true,
        changeYear: true,
        beforeShow: function() {
            if($(this).val().length < 2) {
                var self_item = $('input[name="event[CAL_DATE]"]').index(this) - 1;
                var d = new Date();
                var str = d.getFullYear();
                str += "-"+(((d.getMonth() + 1) < 10) ? "0" + (d.getMonth() + 1) : (d.getMonth() + 1));
                str += "-"+((d.getDate() < 10) ? "0" + d.getDate() : d.getDate()); // tag
                str += "-"+((d.getHours() < 10) ? "0" + d.getHours() : d.getHours());
                str += "-"+((d.getMinutes() < 10) ? "0" + d.getMinutes() : d.getMinutes());
                $(this).val(str);
            }
        },
        onSelect: function() {
            $(this).removeClass('ca-admin-input-date-error');
            var self_item = $('input[name="event[CAL_DATE]"]').index(this) - 1;
            var str = $(this).val();
            for(var i=0;i<cal_search_free.length;i++) {
                if(self_item == i)
                    continue;
                if(cal_search_free[i] === str) {
                    $(this).addClass('ca-admin-input-date-error');
                    break;
                }
            }
         }
/*       onClose: function() {
            $(this).removeClass('ca-admin-input-date-error');
            var self_item = $('input[name="event[CAL_DATE]"]').index(this) - 1;
            var str = $(this).val();
            for(var i=0;i<cal_search_free.length;i++) {
                if(self_item == i)
                    continue;
                if(cal_search_free[i] === str) {
                    $(this).addClass('ca-admin-input-date-error');
                    break;
                }
            }
        }*/
    });
    $('#ui-datepicker-div').addClass('mo-shadow');

    if($('.to-scroll').length > 0) {
        var to_scroll = $('.to-scroll');
        setTimeout(function() {
            to_scroll = Math.ceil((to_scroll.offset().top + (to_scroll.outerHeight() / 2)) - ($(window).height() / 2));
            var item = 'html,body';
            if($.browser.opera)
                item = 'html';
            $(item).animate({scrollTop:to_scroll},300);
        },100);
        $('.to-scroll').removeClass('to-scroll');
    }
});

