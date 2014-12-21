/**
 * jQuery to power Addon installations, activations and deactivations.
 *
 * @package   TGM-Soliloquy
 * @version   1.2.0
 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @copyright Copyright (c) 2012, Thomas Griffin
 */
jQuery(document).ready(function($) {

	/** Re-enable install button if user clicks on it, needs creds but tries to install another addon instead */
	$('#optinmonster-addon-area').on('click.refreshInstallAddon', '.optinmonster-addon-action-button', function(e) {
		var el 		= $(this);
		var buttons = $('#optinmonster-addon-area').find('.optinmonster-addon-action-button');
		$.each(buttons, function(i, element) {
			if ( el == element )
				return true;

			optinmonsterAddonRefresh(element);
		});
	});

	/** Process Addon activations for those currently installed but not yet active */
	$('#optinmonster-addon-area').on('click.activateAddon', '.optinmonster-activate-addon', function(e) {
		e.preventDefault();

		/** Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated */
		$('.optinmonster-addon-error').remove();
		$(this).text(optinmonster_addon.activating);
		$(this).after('<span class="optinmonster-waiting"><img class="optinmonster-spinner" src="' + optinmonster_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: middle;" /></span>');
		var button	= $(this);
		var plugin 	= $(this).attr('rel');
		var el		= $(this).parent().parent();
		var message	= $(this).parent().parent().find('.addon-status');

		/** Process the Ajax to perform the activation */
		var opts = {
			url: 		ajaxurl,
            type: 		'post',
            async: 		true,
            cache: 		false,
            dataType: 	'json',
            data: {
                action: 	'optinmonster_activate_addon',
				nonce: 		optinmonster_addon.activate_nonce,
				plugin:		plugin
            },
            success: function(response) {
            	/** If there is a WP Error instance, output it here and quit the script */
                if ( response && true !== response ) {
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="optinmonster-addon-error"><strong>' + response.error + '</strong></div>');
                		$('.optinmonster-waiting').remove();
                		$('.optinmonster-addon-error').delay(3000).slideUp();
                	});
                	return;
                }

                /** The Ajax request was successful, so let's update the output */
                $(button).text(optinmonster_addon.deactivate).removeClass('optinmonster-activate-addon').addClass('optinmonster-deactivate-addon');
                $(message).text(optinmonster_addon.active);
                $(el).removeClass('optinmonster-addon-inactive').addClass('optinmonster-addon-active');
                $('.optinmonster-waiting').remove();
            },
            error: function(xhr, textStatus ,e) {
                $('.optinmonster-waiting').remove();
                return;
            }
		}
		$.ajax(opts);
	});

	/** Process Addon deactivations for those currently active */
	$('#optinmonster-addon-area').on('click.deactivateAddon', '.optinmonster-deactivate-addon', function(e) {
		e.preventDefault();

		/** Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated */
		$('.optinmonster-addon-error').remove();
		$(this).text(optinmonster_addon.deactivating);
		$(this).after('<span class="optinmonster-waiting"><img class="optinmonster-spinner" src="' + optinmonster_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: middle;" /></span>');
		var button	= $(this);
		var plugin 	= $(this).attr('rel');
		var el		= $(this).parent().parent();
		var message	= $(this).parent().parent().find('.addon-status');

		/** Process the Ajax to perform the activation */
		var opts = {
			url: 		ajaxurl,
            type: 		'post',
            async: 		true,
            cache: 		false,
            dataType: 	'json',
            data: {
                action: 	'optinmonster_deactivate_addon',
				nonce: 		optinmonster_addon.deactivate_nonce,
				plugin:		plugin
            },
            success: function(response) {
            	/** If there is a WP Error instance, output it here and quit the script */
                if ( response && true !== response ) {
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="optinmonster-addon-error"><strong>' + response.error + '</strong></div>');
                		$('.optinmonster-waiting').remove();
                		$('.optinmonster-addon-error').delay(3000).slideUp();
                	});
                	return;
                }

                /** The Ajax request was successful, so let's update the output */
                $(button).text(optinmonster_addon.activate).removeClass('optinmonster-deactivate-addon').addClass('optinmonster-activate-addon');
                $(message).text(optinmonster_addon.inactive);
                $(el).removeClass('optinmonster-addon-active').addClass('optinmonster-addon-inactive');
                $('.optinmonster-waiting').remove();
            },
            error: function(xhr, textStatus ,e) {
                $('.optinmonster-waiting').remove();
                return;
            }
		}
		$.ajax(opts);
	});

	/** Process Addon installations */
	$('#optinmonster-addon-area').on('click.installAddon', '.optinmonster-install-addon', function(e) {
		e.preventDefault();

		/** Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated */
		$('.optinmonster-addon-error').remove();
		$(this).text(optinmonster_addon.installing);
		$(this).after('<span class="optinmonster-waiting"><img class="optinmonster-spinner" src="' + optinmonster_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: middle;" /></span>');
		var button	= $(this);
		var plugin 	= $(this).attr('rel');
		var el		= $(this).parent().parent();
		var message	= $(this).parent().parent().find('.addon-status');

		/** Process the Ajax to perform the activation */
		var opts = {
			url: 		ajaxurl,
            type: 		'post',
            async: 		true,
            cache: 		false,
            dataType: 	'json',
            data: {
                action: 	'optinmonster_install_addon',
				nonce: 		optinmonster_addon.install_nonce,
				plugin:		plugin
            },
            success: function(response) {
            	/** If there is a WP Error instance, output it here and quit the script */
                if ( response.error ) {
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="optinmonster-addon-error"><strong>' + response.error + '</strong></div>');
                		$(button).text(optinmonster_addon.install);
                		$('.optinmonster-waiting').remove();
                		$('.optinmonster-addon-error').delay(4000).slideUp();
                	});
                	return;
                }

                /** If we need more credentials, output the form sent back to us */
                if ( response.form ) {
                	/** Display the form to gather the users credentials */
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="optinmonster-addon-error">' + response.form + '</div>');
                		$('.optinmonster-waiting').remove();
                	});

                	/** Add a disabled attribute the install button if the creds are needed */
                	$(button).attr('disabled', true);

                	$('#optinmonster-addon-area').on('click.installCredsAddon', '#upgrade', function(e) {
                		/** Prevent the default action, let the user know we are attempting to install again and go with it */
                		e.preventDefault();
                		$('.optinmonster-waiting').remove();
                		$(this).val(optinmonster_addon.installing);
                		$(this).after('<span class="optinmonster-waiting"><img class="optinmonster-spinner" src="' + optinmonster_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: text-bottom;" /></span>');

                		/** Now let's make another Ajax request once the user has submitted their credentials */
                		var hostname 	= $(this).parent().parent().find('#hostname').val();
                		var username	= $(this).parent().parent().find('#username').val();
                		var password	= $(this).parent().parent().find('#password').val();
                		var proceed		= $(this);
                		var connect		= $(this).parent().parent().parent().parent();
                		var cred_opts 	= {
                			url: 		ajaxurl,
            				type: 		'post',
            				async: 		true,
            				cache: 		false,
            				dataType: 	'json',
            				data: {
                				action: 	'optinmonster_install_addon',
								nonce: 		optinmonster_addon.install_nonce,
								plugin:		plugin,
								hostname:	hostname,
								username:	username,
								password:	password
            				},
            				success: function(response) {
            					/** If there is a WP Error instance, output it here and quit the script */
                				if ( response.error ) {
                					$(el).slideDown('normal', function() {
                						$(button).after('<div class="optinmonster-addon-error"><strong>' + response.error + '</strong></div>');
										$(button).text(optinmonster_addon.install);
                						$('.optinmonster-waiting').remove();
                						$('.optinmonster-addon-error').delay(4000).slideUp();
                					});
                					return;
                				}

                				if ( response.form ) {
                					$('.optinmonster-waiting').remove();
                					$('.optinmonster-inline-error').remove();
                					$(proceed).val(optinmonster_addon.proceed);
                					$(proceed).after('<span class="optinmonster-inline-error">' + optinmonster_addon.connect_error + '</span>');
                					return;
                				}

                				/** The Ajax request was successful, so let's update the output */
                				$(connect).remove();
                				$(button).show();
                				$(button).text(optinmonster_addon.activate).removeClass('optinmonster-install-addon').addClass('optinmonster-activate-addon');
                				$(button).attr('rel', response.plugin);
                				$(button).removeAttr('disabled');
                				$(message).text(optinmonster_addon.inactive);
                				$(el).removeClass('optinmonster-addon-not-installed').addClass('optinmonster-addon-inactive');
                				$('.optinmonster-waiting').remove();
            				},
            				error: function(xhr, textStatus ,e) {
                				$('.optinmonster-waiting').remove();
                				return;
            				}
                		}
                		$.ajax(cred_opts);
                	});

                	/** No need to move further if we need to enter our creds */
                	return;
                }

                /** The Ajax request was successful, so let's update the output */
                $(button).text(optinmonster_addon.activate).removeClass('optinmonster-install-addon').addClass('optinmonster-activate-addon');
                $(button).attr('rel', response.plugin);
                $(message).text(optinmonster_addon.inactive);
                $(el).removeClass('optinmonster-addon-not-installed').addClass('optinmonster-addon-inactive');
                $('.optinmonster-waiting').remove();
            },
            error: function(xhr, textStatus ,e) {
                $('.optinmonster-waiting').remove();
                return;
            }
		}
		$.ajax(opts);
	});

	/** Function to clear any disabled buttons and extra text if the user needs to add creds but instead tries to install a different addon */
	function optinmonsterAddonRefresh(element) {
		if ( $(element).attr('disabled') )
			$(element).removeAttr('disabled');

		if ( $(element).parent().parent().hasClass('optinmonster-addon-not-installed') )
			$(element).text(optinmonster_addon.install);
	}

});