var stupidtable_asc = "asc";

$(function(){
//(function($) {

$.fn.stupidtable({
        "caldate":function(a,b){
//console.log("drin")
            if(a.length < 16)
                a += (stupidtable_asc === "asc" ? "0000-01-01-01-00" : "0000-12-31-23-60" ).substr(a.length);
            if(b.length < 16)
                b += (stupidtable_asc === "asc" ? "0000-01-01-01-00" : "0000-12-31-23-60" ).substr(b.length);
            if(stupidtable_asc === "asc") {
                if (a < b) return -1;
                if (a > b) return +1;
            } else {
                if (a < b) return +1;
                if (a > b) return -1;
            }
            return 0;
        }
    });

/*
    $(stupidtable_item).stupidtable({
        "caldate":function(a,b){
            if(a.length < 16)
                a += (stupidtable_asc === "asc" ? "0000-01-01-01-00" : "0000-12-31-23-60" ).substr(a.length);
            if(b.length < 16)
                b += (stupidtable_asc === "asc" ? "0000-01-01-01-00" : "0000-12-31-23-60" ).substr(b.length);
            if(stupidtable_asc === "asc") {
                if (a < b) return -1;
                if (a > b) return +1;
            } else {
                if (a < b) return +1;
                if (a > b) return -1;
            }
            return 0;
        }
    });*/
    $("th[data-sort]").addClass("sorting").eq(0).trigger("click");
});
