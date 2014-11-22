function showPrevNext(element,para,elem) {
    $.get(element.href+para,
        function(data) {
            $(element).parents(elem).replaceWith(data);
        },"html");
}

