$(document).ready(function() {

    $('#changePage').change(function(){
        $('#nbpage').val($(this).val());
    });
/*
var $rows = $('#table tr');
$('#search').keyup(debounce(function() {
    var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

    $rows.show().filter(function() {
        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
        return !~text.indexOf(val);
    }).hide();
}, 300));
});*/

    var $rows = $('#table tbody tr');
    $('#search').keyup(debounce(function() {
        var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase().split(' ');

        $rows.hide().filter(function() {
            var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
            var matchesSearch = true;
            $(val).each(function(index, value) {
                matchesSearch = (!matchesSearch) ? false : ~text.indexOf(value);
            });
            return matchesSearch;
        }).show();
    }));
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
});