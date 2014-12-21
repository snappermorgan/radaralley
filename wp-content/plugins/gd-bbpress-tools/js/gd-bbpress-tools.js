/*jslint regexp: true, undef: true, sloppy: true, eqeq: true, vars: true, white: true, plusplus: true, maxerr: 50, indent: 4 */
var gdbbPressTools = {
    storage: { },
    get_selection: function() {
        var t = '';

        if (window.getSelection){
            t = window.getSelection();
        } else if (document.getSelection){
            t = document.getSelection();
        } else if (document.selection){
            t = document.selection.createRange().text;
        }

        return jQuery.trim(t.toString());
    },
    init: function() {
        jQuery(document).on("click", ".d4p-bbt-quote-link", function(e){
            e.preventDefault();

            if (jQuery("#bbp_reply_content").length > 0) {
                var qout = gdbbPressTools.get_selection();
                var id = jQuery(this).attr("href").substr(1);
                var quote_id = '#d4p-bbp-quote-' + id;

                if (qout === "") {
                    qout = jQuery(quote_id).html();
                }

                qout = qout.replace(/&nbsp;/g, " ");
                qout = qout.replace(/<p>|<br>/g, "");
                qout = qout.replace(/<\/\s*p>/g, "\n");

                if (gdbbPressToolsInit.quote_method === "bbcode") {
                    qout = "[quote=" + id + "]" + qout + "[/quote]";
                } else {
                    var title = '<div class="d4p-bbp-quote-title"><a href="' + jQuery(this).attr("bbp-url") + '">';
                    title+= jQuery(this).attr("bbp-author") + ' ' + gdbbPressToolsInit.quote_wrote + ':</a></div>';
                    qout = '<blockquote class="d4pbbc-quote">' + title + qout + '</blockquote>';
                }

                if (gdbbPressToolsInit.wp_editor == 1 && !jQuery("#bbp_reply_content").is(":visible")) {
                    if (gdbbPressToolsInit.wp_version > 38) {
                        tinymce.get("bbp_reply_content").execCommand("mceInsertContent", false, qout);
                    } else {
                        tinyMCE.execInstanceCommand("bbp_reply_content", "mceInsertContent", false, qout);
                    }
                } else {
                    var txtr = jQuery("#bbp_reply_content");
                    var cntn = txtr.val();

                    if (jQuery.trim(cntn) != '') {
                        qout = "\n\n" + qout;
                    }

                    txtr.val(cntn + qout);
                }

                var old_ie = jQuery.browser.msie && parseInt(jQuery.browser.version) < 9;

                if (!old_ie) {
                    jQuery("html, body").animate({scrollTop: jQuery("#new-post").offset().top}, 1000);
                } else {
                    document.location.href = "#new-post";
                }
            }
        });
    }
};

jQuery(document).ready(function() {
    gdbbPressTools.init();
});
