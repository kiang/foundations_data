var foundations = [];
$(function () {
    $.getJSON('history.json', {}, function (c) {
        var links = '';
        for (k in c) {
            links += '<div class="col-md-2"><a href="' + k + '.html">' + k + '</a></div>';
        }
        $('#history').append(links);
    });
    $.get('../../../dbKeys.csv', {}, function(c) {
        var lines = c.split(/\r?\n/);
        for(k in lines) {
            var cols = lines[k].split(',');
            foundations.push({
                value: cols[1],
                label: cols[0]
            });
        }
        $('input#keyword').autocomplete({
            minLength: 2,
            source: foundations,
            select: function(event, ui) {
                location.href = baseUrl + 'foundations/view/' + ui.item.value + '/index.html';
            }
        });
    });
    $('a.btn-foundation').click(function(e) {
        e.preventDefault();
        return false;
    });
})