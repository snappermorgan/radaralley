;(function($) {
    $(document).ready(function() {
        if (typeof HideSettingRow === 'function') {

            var ToggleUserLimitEntry = function (){
                console.log('ok');
                if($("#gform_limit_user_entries").is(":checked")){
                    ShowSettingRow('#limit_user_entries_settings');
                }
                else{
                    HideSettingRow('#limit_user_entries_settings');
                }
            }
            $("#gform_limit_user_entries").on('change', function() {
                ToggleUserLimitEntry();
            })
            ToggleUserLimitEntry();
        }
    });

})(jQuery);