jQuery(function($) { 

	// Init
	$(document).ready(function(){
		$('#products_grid').mixitup();
		loadHelpdesk();
	});

	var loadHelpdesk = function() {
		try {
			var c = document.createElement('link');
			c.rel = "stylesheet";
			c.type = "text/css";
			c.href = "//assets.zendesk.com/external/zenbox/v2.6/zenbox.css";
			var h = document.getElementsByTagName('head')[0];
			h.appendChild(c);
			$.ajax({
				url: "//assets.zendesk.com/external/zenbox/v2.6/zenbox.js",
				dataType: "script",
				cache: true,
				success: function() {
					Zenbox.init({
				      dropboxID:   "20219226",
				      url:         "https://wpavengers.zendesk.com",
				      tabTooltip:  "Support",
				      tabImageURL: "https://assets.zendesk.com/external/zenbox/images/tab_support_right.png",
				      tabColor:    "#007bbd",
				      tabPosition: "Right"
				    });
				}
			});
		} catch ( e ) { 
		}
	};

	$('#cb-select-all').change(function(){
        var checkboxes = $("#products_grid").find(':visible').find(':checkbox');
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true);
        } else {
          checkboxes.prop('checked', false);
        }
    });

    $('#doaction').on('click', function(){
		var action = $("select[name='bulk_actions']").val();
		var batch = [];
		var ftp = {};
		if (action && action != "") {
			$("#products_grid").find(':visible').find('input:checkbox:checked').each( function(i, el) {
				
				var plugin = $(this).val(),
					from = $(this).data("file"),
					version = $(this).data("version"),
					conflicts = $(this).data("conflicts"),
					installed = ($(this).data("activation-status") != 'new'),
					update_status = $(this).data("update-status"),
					updated = (update_status == 'updated' || update_status == 'new');
					

				switch(action) {
					case 'install_update_activate':
						// conflicts...
						if (conflicts != undefined && conflicts != '') {
							batch.push({"action": "deactivate", "args": {"plugin": conflicts} });
						}
						if (installed && updated) {
							if (plugin.indexOf('dashboard-wpa') == -1) {
								batch.push({"action": "deactivate", "args": {"plugin": plugin} });
							}
							batch.push({"action": "upgrade-plugin", "args": {"plugin": plugin} });
						} else if (updated) {
							batch.push({"action": "install-plugin", "args": {"from": wpa_api_endpoint + from + '?v=' + version } });
						}
						if (plugin.indexOf('dashboard-wpa') == -1) {
							batch.push({"action": "activate", "args": {"plugin": plugin} });
						}
						if ( $('#ftp-wrap').is(':visible') ) {
							ftp = {
								hostname: $('#hostname').val(),
								username: $('#username').val(),
								password: $('#password').val(),
								connection_type: $('input[name=connection_type]:checked').val()
							};
							$('#ftp-wrap').slideUp();
						}
						break;
					case 'deactivate':
						if (plugin.indexOf('dashboard-wpa') == -1) {
							batch.push({"action": "deactivate", "args": {"plugin": plugin} });
						}
						break;
					case 'activate':
						if (conflicts != undefined && conflicts != '') {
							batch.push({"action": "deactivate", "args": {"plugin": conflicts} });
						}
						batch.push({"action": "activate", "args": {"plugin": plugin} });
						break;
				}
			});
			
			if (batch.length > 0) {
				var data = {
					action: 'wpa-batch',
					wpa_nonce: wpa_nonce,
					batch: JSON.stringify(batch)
				};

				if ( !$.isEmptyObject( ftp ) ) {
					$.extend( data, ftp );
				}
				
				$("#message").html("").removeClass("error");
				$( "#actions_progressbar" ).show();
				$.post(ajaxurl, data, function(response) {
			    	$( "#actions_progressbar" ).hide();
			    	var errors = [];
			    	try {
						result = $.parseJSON(response);	
						$.each(result, function( idx, el ) {
							if (el.result == false && el.msg) {
								errors.push('<p>'+el.msg+'</p>');
							}
						});
					} catch (e) {
						// Hack for FTP credentials error
						if (response.indexOf("form") > 0) {
							//console.log(response);
							response = response.replace('<input type="submit"', '<input type="button" onclick="jQuery(\'#doaction\').trigger(\'click\');"');
							$('#ftp-wrap').html(response);
							$('#ftp-wrap').slideDown();
							return;
						} else {
							errors.push('<p><pre>'+response+'</pre></p>');
						}
					}
					if (errors.length) {
						$("#message").html(errors.join("\n")).addClass("error");
					} else {
						location.reload();
					}
				});
			}
			
		} else {
			alert("Please select an action first.");
		}
		return false;
	});

});