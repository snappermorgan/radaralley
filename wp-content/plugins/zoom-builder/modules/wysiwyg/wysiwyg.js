var tinyMCEPreInit;
var wpActiveEditor;
(function ($) {
    function visual_editor(id) {
        $( '#' + id ).addClass( 'mceEditor' );
        if ( typeof tinyMCE == 'object' && typeof tinyMCE.execCommand == 'function' ) {
            html_editor( id );
            tinyMCEPreInit.mceInit[id] = tinyMCEPreInit.mceInit['wpzoom-wysiwyg-widget'];
            tinyMCEPreInit.mceInit[id]['selector'] = '#' + id;
            try {
                // Instantiate new TinyMCE editor
                tinymce.init( tinymce.extend( {}, tinyMCEPreInit.mceInit['wpzoom-wysiwyg-widget'], tinyMCEPreInit.mceInit[id] ) );
                tinyMCE.execCommand( 'mceAddControl', false, id );
            } catch( e ) {
                alert( e );
            }
            if ( typeof tinyMCE.get( id ).on == 'function' ) {
                tinyMCE.get( id ).on( 'keyup change', function() {
                    var content = tinyMCE.get( id ).getContent();
                    $( 'textarea#' + id ).val( content ).change();
                });
            }
        }
    }

    function html_editor(id) {
        if ( typeof tinyMCE == 'object' && typeof tinyMCE.execCommand == 'function' ) {
            if ( tinyMCE.get( id ) != null && typeof tinyMCE.get( id ).getContent == 'function' ) {
                var content = tinyMCE.get( id ).getContent();
                tinyMCE.get( id ).remove();
                $( 'textarea#' + id ).val( content );
            }
        }
    }

    function init(id) {
        $("div.widget-inside:has(#" + id + ") input[id^=widget-wpzoom-wysiwyg][id$=type][value=visual]").each(function () {
            if ($("div.widget:has(#" + id + ") :animated").size() == 0 && typeof (tinyMCE.get(id)) != "object" && $("#" + id).is(":visible")) {
                $("a[id^=widget-wpzoom-wysiwyg][id$=visual]", $(this).closest("div.widget-inside")).click()
            } else {
                if (typeof (tinyMCE.get(id)) != "object") {
                    setTimeout(function () {
                        init(id);
                        id = null
                    }, 100)
                } else {
                    $("a[id^=widget-wpzoom-wysiwyg][id$=visual]", $(this).closest("div.widget-inside")).click()
                }
            }
        })
    }

    function ajax_init(id) {
        $( 'div.widget-inside:has(#' + id + ') input[id^=widget-wpzoom-wysiwyg][id$=type][value=visual]' ).each(function() {
            // If textarea is visible and animation/ajax has completed then trigger a click to Visual button and enable the editor
            if ( $.active == 0 && tinyMCE.get( id ) == null && $( '#' + id ).is( ':visible' ) ) {
                $( 'a[id^=widget-wpzoom-wysiwyg][id$=visual]', $( this ).closest( 'div.widget-inside' ) ).click();
            }
            // Otherwise wait and retry later (animation ongoing)
            else if ( $( 'div.widget:has(#' + id + ') div.widget-inside' ).is( ':visible' ) && tinyMCE.get( id ) == null ) {
                setTimeout(function() {
                    ajax_init( id );
                    id=null;
                }, 100 );
            }
        });
    }

    $(document).ready(function () {
        $(document).on("click", "div.widget:has(textarea[id^=widget-wpzoom-wysiwyg]) .widget-top", function () {
            var context = $(this).closest("div.widget");
            var textarea = $("textarea[id^=widget-wpzoom-wysiwyg]", context);

            $("input[name=savewidget]", context).on("click", function (j) {

                var l = $(this).closest("div.widget");
                var k = $("textarea[id^=widget-wpzoom-wysiwyg]", l);
                if (typeof (tinyMCE.get(k.attr("id"))) == "object") {
                    html_editor(k.attr("id"))
                }
                $(this).unbind("ajaxSuccess").ajaxSuccess(function (n, o, m) {
                    var p = $("textarea[id^=widget-wpzoom-wysiwyg]", $(this).closest("div.widget-inside"));
                    ajax_init(p.attr("id"))
                })
            });

            $("#wpbody-content").css("overflow", "visible");
            context.css("position", "relative").css("z-index", "100");
            init(textarea.attr("id"));
            $(".insert-media", context).data("editor", textarea.attr("id"))
        });

        $("div.widget[id*=widget-wpzoom-wysiwyg] input[name=savewidget]").on("click", function (g) {
            var i = $(this).closest("div.widget");
            var h = $("textarea[id^=widget-wpzoom-wysiwyg]", i);
            if (typeof (tinyMCE.get(h.attr("id"))) == "object") {
                html_editor(h.attr("id"))
            }
            $(this).unbind("ajaxSuccess").ajaxSuccess(function (k, l, j) {
                var m = $("textarea[id^=widget-wpzoom-wysiwyg]", $(this).closest("div.widget-inside"));
                ajax_init(m.attr("id"))
            })
        });

        $(document).on("click", "a[id^=widget-wpzoom-wysiwyg][id$=visual]", function (g) {
            var h = $(this).closest("div.widget-inside");
            $("input[id^=widget-wpzoom-wysiwyg][id$=type]", h).val("visual");
            $(this).addClass("active");
            $("a[id^=widget-wpzoom-wysiwyg][id$=html]", h).removeClass("active");
            visual_editor($("textarea[id^=widget-wpzoom-wysiwyg]", h).attr("id"))
        });
        $(document).on("click", "a[id^=widget-wpzoom-wysiwyg][id$=html]", function (g) {
            var h = $(this).closest("div.widget-inside");
            $("input[id^=widget-wpzoom-wysiwyg][id$=type]", h).val("html");
            $(this).addClass("active");
            $("a[id^=widget-wpzoom-wysiwyg][id$=visual]", h).removeClass("active");
            html_editor($("textarea[id^=widget-wpzoom-wysiwyg]", h).attr("id"))
        });
        $(document).on("click", ".editor_media_buttons a", function () {
            var g = $(this).closest("div.widget-inside");
            wpActiveEditor = $("textarea[id^=widget-wpzoom-wysiwyg]", g).attr("id")
        });
        if ($("body.widgets_access").size() > 0) {
            var f = $("textarea[id^=widget-wpzoom-wysiwyg]");
            init(f.attr("id"))
        }
    })
})(jQuery);