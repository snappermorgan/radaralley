jQuery(document).ready(function($){
    $('.create-wallet-button-ajax').on('click', function(e){
        var createButton = this;
        e.preventDefault();
        $.ajax({
            url: data.ajaxurl,
            type: 'post',
            data: {
                action : 'create_wallet_id'
            },
            success: function(response) {
                var parsedResponse = JSON.parse(response);

                if(parsedResponse.success) {
                    $('.wallet_id_bar .createdWalletID').html(parsedResponse.wallet_name);
                    $('.wallet_id_bar').show();
                }

                if(data.hideCreateButtonAfterAction == 1) {
                    $(createButton).hide();
                }
            }
        });
    });

    $('#provide-wallet-id').submit(function(){
        var wallet_id = $('#wallet_id').val();
        var form = this;

        if(wallet_id == '') {
            alert(data.l18n.missing_wallet_id);
        }

        $.ajax({
            url: data.ajaxurl,
            type: 'post',
            data: {
                action : 'get_wallet_info',
                wallet_id : $('#wallet_id').val()
            },
            success: function(response) {
                var parsedResponse = JSON.parse(response);
                if(parsedResponse.success) {

                    if($('.wallet_info_container').length > 0) {
                        $('.wallet_info_container').remove();
                    }

                    $(form).after(parsedResponse.content);
                    $('.wallet_transactions_body table').tablesorter();
                } else {
                    alert(parsedResponse.error);
                }
            }
        });
        return false;
    });

    $('#cm-micropayments-checkout-form-submit').click(function(e){
        var walletId = $('#wallet_id').val();
        var form = $('#cm-micropayments-checkout-form');
        e.preventDefault();

        if(walletId != '') {
            $.ajax({
                url: data.ajaxurl,
                type: 'post',
                data: {
                    action : 'check_wallet_id',
                    wallet_id : walletId
                },
                success: function(response) {
                    var resp = JSON.parse(response);
                    if(resp.success) {
                        $(form).submit();
                    } else {
                        $('.entry-content .error').html(resp.message);
                    }
                }
            });
        }
    });
});
