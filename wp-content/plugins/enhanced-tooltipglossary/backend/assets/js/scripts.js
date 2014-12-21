jQuery(document).ready(function($){
    $('.inlineEditButton').on('click', function() {
        var td = $(this).parent().parent().parent();
        $(td).hide().siblings().hide();

        $(td).parent().append('<td  class="edit-form-wallet-points" colspan="5">' +
            '<form class="form-wallet">' +
            '<input type="hidden" name="wallet_id" id="wallet_id"/>' +
                '<table class="form-table">' +
                    '<tbody>'+
                        '<tr class="form-field">' +
                            '<th scope="row" valign="top">' +
                                '<label for="points">' + data.l18n.label + '</label>' +
                            '</th>' +
                            '<td>' +
                                '<input type="text" name="points" id="points" style="width:200px">' +
                            '</td>' +
                        '</tr>' +
                    '</tbody>' +
                '</table>' +
                '<p class="submit"> ' +
                    '<a class="button-secondary cancel alignleft" accesskey="c">' + data.l18n.cancel + '</a>' +
                '   <a class="button-primary save alignright">' + data.l18n.save + '</a>' +
                '</p>' +
            '</form>' +
            '</td>');

        $('input#wallet_id').val(parseInt($(td).parent().find('td.wallet_id').html()));
        $('input#points').val(parseInt($(td).parent().find('td.points').html()));
        $('form.form-wallet').submit(function(e) {
            e.preventDefault();
            submitChangePointsForm();
        });

        $('.edit-form-wallet-points .cancel').on('click', function(){
            $('.edit-form-wallet-points').remove();
            $(td).show().siblings().show();
        });

        submitChangePointsForm();

        function submitChangePointsForm() {
            $('.edit-form-wallet-points .save').on('click', function(){
                $.ajax({
                    url: data.ajaxurl,
                    data : $('.form-wallet').serialize(),
                    success : function(response) {
                        var resp = JSON.parse(response);
                        if(resp.error == undefined) {
                            $(td).show().siblings().show();
                            $('.edit-form-wallet-points').remove();
                            (td).parent().find('td.points').html(resp.points);
                        } else {
                            alert(resp.error);
                        }
                    }
                });
            });
        }
    });
    $("<div id='tooltip'></div>").css({
        position: "absolute",
        display: "none",
        border: "1px solid #fdd",
        padding: "2px",
        "background-color": "#fee",
        opacity: 0.80
    }).appendTo("body");

    $("#cm-micropayment-report-placeholder").bind("plothover", function (event, pos, item) {

        var str = "(" + pos.x.toFixed(2) + ", " + pos.y.toFixed(2) + ")";
        $("#hoverdata").text(str);

        if (item) {
            var x = item.datapoint[0].toFixed(2),
                y = item.datapoint[1].toFixed(2);

            $("#tooltip").html(y)
                .css({top: item.pageY+5, left: item.pageX+5})
                .fadeIn(200);
        } else {
            $("#tooltip").hide();
        }
    });

    $("#cm-micropayment-report-placeholder").bind("plotclick", function (event, pos, item) {
        if (item) {
            $("#clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
            plot.highlight(item.series, item.datapoint);
        }
    });

    $('#only_successful').bind('change', function(){
        $('#cm-filter-form').submit();
    });

});
