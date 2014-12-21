function ApplyCouponCode(formId){

    var couponCode = jQuery('#gf_coupon_code_' + formId).val();
    if(couponCode === 'undefined' || couponCode == '')
        return;

    jQuery('#gf_coupons_container_' + formId + ' #gf_coupon_spinner').show();
    jQuery('#gf_coupons_container_' + formId + ' #gf_coupon_button').prop("disabled", true);

    jQuery.post(ajaxurl, {
        action : "gf_apply_coupon_code",
        couponCode : couponCode,
        existing_coupons : jQuery('#gf_coupon_codes_' + formId).val(),
        formId : formId,
        total : jQuery('#gf_total_no_discount_' + formId).val()
    },

    function(response) {

        var couponInfo = jQuery.parseJSON(response);

        jQuery('#gf_coupons_container_' + formId + ' .gf_coupon_invalid').remove();
        jQuery('#gf_coupon_code_' + formId).val('');

        if(!couponInfo["is_valid"]){
            jQuery('#gf_coupons_container_' + formId + ' #gf_coupon_info').prepend("<div class='gf_coupon_invalid'><span>" + couponInfo["invalid_reason"] + "</span></div>");
        }
        else
        {

            window["gf_coupons" + formId] = couponInfo["coupons"];

            //setting hidden field with list of coupons
            var coupon_codes = "";
            var couponsString = "";
            var i=0;
            for (coupon in window["gf_coupons" + formId])
            {
                if(i>0)
                    coupon_codes += ", ";

                coupon_codes += window["gf_coupons" + formId][coupon]["code"];

                i++;
            }

            jQuery('#gf_coupon_codes_' + formId).val(coupon_codes);
            jQuery("#gf_coupons_" + formId).val(jQuery.toJSON(window["gf_coupons" + formId]));

            gformCalculateTotalPrice(formId);

        }

        jQuery('#gf_coupons_container_' + formId + ' #gf_coupon_spinner').hide();

    }
    );

}

function GetDiscount(couponType, couponAmount, price, totalDiscount) {
    price = price - totalDiscount;
    if(couponType == 'flat') {
        discount = Number(couponAmount);
    }
    else {
        discount = price * Number((couponAmount / 100));
    }

    return discount;
}

function PopulateDiscountInfo(price,formId){

    var couponDetails = "";
    var totalDiscount = 0;
    var couponInfo = "";
    var currency = new Currency(gf_global['gf_currency_config']);


    if(window["gf_coupons" + formId] === undefined)
        window["gf_coupons" + formId] = jQuery.evalJSON(jQuery('#gf_coupons_' + formId).val());

        for (code in window["gf_coupons" + formId])
        {
            coupon = window["gf_coupons" + formId][code];
            couponDiscount = GetDiscount(coupon["type"], coupon["amount"] , price, totalDiscount);
            totalDiscount += couponDiscount;
            couponDetails += '<tr class="gf_coupon_item" id="gf_coupon_' + coupon["code"] + '"><td class="gf_coupon_name_container">' +
            				'   <a href="javascript:void(0);" onclick="DeleteCoupon(\'' + coupon["code"] + '\' , \'' + formId + '\');">(x)</a>' +
                            '   <span class="gf_coupon_name">' + coupon["name"] + '</span>' +
                            '</td><td class="gf_coupon_discount_container">' +
                            '   <span class="gf_coupon_discount">-' + currency.toMoney(couponDiscount) + '</span>' +
                            '</td></tr>';
        }


    jQuery('#gf_coupons_container_' + formId + ' #gf_coupon_info').html("<table>"+couponDetails+"</table>");
    return totalDiscount;

}

function DisableApplyButton(formId){
    var is_disabled = window["new_total_" + formId] == 0 || jQuery("#gf_coupons_container_" + formId + " #gf_coupon_code_" + formId).val() == "";

    if(is_disabled)
        jQuery("#gf_coupons_container_" + formId + " #gf_coupon_button").prop("disabled", true);
    else
        jQuery("#gf_coupons_container_" + formId + " #gf_coupon_button").prop("disabled", false);
}

function gform_product_total(formId, total){
    //ignore forms that don't have a coupon field
    if(jQuery('#gf_coupon_code_' + formId).length == 0)
        return total;

    jQuery("#gf_total_no_discount_" + formId).val(total);

    var coupon_code = gformIsHidden(jQuery('#gf_coupon_code_' + formId)) ? "" : jQuery('#gf_coupon_codes_' + formId).val();
    var has_coupon = coupon_code != "" || jQuery("#gf_coupons_" + formId).val() != "";

    window["new_total_" + formId] = total;
    new_total = window["new_total_" + formId];

    if(has_coupon){
        var total_discount = 0;
        total_discount = PopulateDiscountInfo(total,formId);
        new_total = total - total_discount;
    }

    jQuery("#gf_coupons_container_" + formId + " #gf_coupon_spinner").hide();

    DisableApplyButton(formId);

    return new_total;
}

function DeleteCoupon (code,formId){

    //check if coupon code is in the process of being applied
    if(jQuery("#gf_coupons_container_" + formId + " #gf_coupon_spinner").is(':visible'))
        return;

    //removing coupon from UI
    jQuery("#gf_coupons_container_" + formId + " #gf_coupon_" + code).remove();
    jQuery("#gf_coupons_container_" + formId + " #gf_coupon_spinner").show();
    jQuery("#gf_coupons_container_" + formId + " #gf_coupon_button").prop("disabled", true);

    //removing coupon from hidden field
    var coupon_codes = jQuery("#gf_coupon_codes_" + formId).val().split(', ');
    var index = jQuery.inArray(code, coupon_codes);
    if(index == -1)
        return;

    coupon_codes.splice(index, 1);
    jQuery("#gf_coupon_codes_" + formId).val(coupon_codes.join(", "));

    var coupon;
    var tmpArray = new Array();
    for(coupon in window["gf_coupons" + formId])
    {
        if(window["gf_coupons" + formId][coupon]["code"] != code)
        {
            tmpArray[coupon] = window["gf_coupons" + formId][coupon];
        }
    }
    window["gf_coupons" + formId] = tmpArray;

    gformCalculateTotalPrice(formId);

}