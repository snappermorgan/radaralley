function OptinMonster(){
    // Method to hold publicly accessible object properties.
    this.public = {
        // Prepare custom object properties.
        optin   : '',
        _optin  : '',
        id      : '',
        delay   : 0,
        expires : 7,
        second  : false,
        exit    : false,
        type    : '',
        theme   : '',
        html    : '',

        // Prepare defined object properties.
        jQuery  : false,
        ejQuery : false, // eJquery is used for registering events to the global jQuery object so it can be accessed by external scripts.
        url     : '',
        timer   : 0,
        ajax    : false,
        manual  : false,
    },

    // The init method that loads the object class.
    this.init = function(params){
        // Set the optin properties.
        for ( key in params ) this.public[key] = params[key];

        // If cookies are not enabled, we can't reliably determine optin visibility, so return early.
        if ( ! this.cookiesEnabled() )
            return;

        // If we have a cookie set and it is valid, bail out and do nothing.
        var global_cookie = this.getCookie('om-global-cookie'),
            cookie        = this.getCookie('om-' + this.public.optin);
        if ( global_cookie || cookie && ! (new RegExp('slide')).test(this.public.optin) || this.isMobile() )
            return;

        // Possibly load jQuery and handle the rest of the output based on response.
        this.loadjQuery();
    },

    // Manual init method for triggering manual lightboxes.
    this.manualInit = function(params){
        // Set the optin properties.
        for ( key in params ) this.public[key] = params[key];

        // Set the manual property to true.
        this.public.manual = true;

        // Possibly load jQuery and handle the rest of the output based on response.
        this.loadjQuery();
    },

    // Checks to see if jQuery is loaded and if it is a high enough version; if not, jQuery is loaded.
    this.loadjQuery = function(){
        // Store localized copy of our main object instance.
        var self = this;

        // If jQuery is not present or not the correct version, load it asynchronously and fire the rest of the app once jQuery has loaded.
        if ( window.jQuery === undefined ) {
            var om    = document.createElement('script');
            om.src    = '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js';
            om.onload = om.onreadystatechange = function(){
                var s = this.readyState;
                if (s) if (s != 'complete') if (s != 'loaded') return;
                try {
                    self.loadjQueryHandler(false);
                } catch(e){}
            };

            // Attempt to append it to the <head>, otherwise append to the document.
            (document.getElementsByTagName('head')[0] || document.documentElement).appendChild(om);
        } else if ( window.jQuery.fn.jquery !== '1.10.2' ) {
            // Set the window jQuery property for event triggering.
            this.public.ejQuery = window.jQuery;

            // jQuery exists, but it is not the version we want, so we need to manage duplicate jQuery objects.
            var om    = document.createElement('script');
            om.src    = '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js';
            om.onload = om.onreadystatechange = function(){
                var s = this.readyState;
                if (s) if (s != 'complete') if (s != 'loaded') return;
                try {
                    self.loadjQueryHandler(true);
                } catch(e){}
            };

            // Attempt to append it to the <head>, otherwise append to the document.
            (document.getElementsByTagName('head')[0] || document.documentElement).appendChild(om);
        } else {
            // The version of jQuery loaded into the window is what we want to use, so we can just load the handler and output content.
            self.public.jQuery = self.public.ejQuery = window.jQuery;
            self.loadApp();
        }
    },

    // Stores a localized copy of jQuery and removes any potential conflicts with other jQuery libraries.
    this.loadjQueryHandler = function(exists){
        // If jQuery already exists, don't overwrite the global but set noConflict to properly handle multiple versions.
        if ( exists ) {
            // Don't set global jQuery object - just store in our property.
            this.public.jQuery = window.jQuery.noConflict(true);
            this.loadApp();
        } else {
            // Store the global jQuery object since it does not exist yet.
            jQuery = window.jQuery.noConflict(true);
            this.public.jQuery = this.public.ejQuery = jQuery;
            this.loadApp();
        }
    },

    // Callback to load the content of our app into the page.
    this.loadApp = function(){
        // Store localized copy of our main object instance.
        var self = this;

        // Woot, we now have jQuery!
        this.public.jQuery(document).ready(function($){
            // Go ahead and hide our custom container.
            $('#om-' + self.public.optin).css({ 'display' : 'none' });

            // Load jQuery placeholder asynchronously while grabbing data.
            self.loadPlaceholder();

            // Request our optin data via JSONP from the info provided.
            $.ajax({ url: self.public.ajax, data: { action: 'load_optinmonster', optin: self.public.optin, referer: window.location.href, user_agent: navigator.userAgent }, cache: false, async: true, type: 'POST', dataType: 'json', timeout: 10000,
                success: function(resp){
                    // If for some reason we do not receive a response or the response is empty, silently fail.
                    if ( ! resp || $.isEmptyObject(resp) ) return;

                    // If the hash received is different than the one specified, we are using a clone, so we need to update accordingly.
                    if ( resp.hash ) {
                        if ( self.public.optin !== resp.hash ) {
                            // Store the original parent optin and apply clone to current optin.
                            self.public._optin = self.public.optin;
                            self.public.optin  = resp.hash;

                            // Update the holder with the clone ID.
                            $('#om-' + self.public._optin).attr('id', 'om-' + self.public.optin).css({ 'display' : 'none' });
                        }
                    }

                    // If we have received a cookie response, set it now.
                    if ( resp.cookie )
                        self.public.expires = parseInt(resp.cookie);

                    // If we have received a duration response, set it now.
                    if ( resp.delay )
                        self.public.delay = parseInt(resp.delay);

                    // If we have received an ID response, set it now.
                    if ( resp.id )
                        self.public.id = resp.id;

                    // If we have received an HTML response, set it now.
                    if ( resp.html )
                        self.public.html = resp.html;

                    // If we have received an type response, set it now.
                    if ( resp.type )
                        self.public.type = resp.type;

                    // If we have received an theme response, set it now.
                    if ( resp.theme )
                        self.public.theme = resp.theme;

                    // If we have received a second response, set it now.
                    if ( resp.second )
                        self.public.second = resp.second;

                    // If we have received an exit response, set it now.
                    if ( resp.exit )
                        self.public.exit = resp.exit;

                    // If the custom property has been set, set it now.
                    if ( resp.custom )
                        self.public.custom = resp.custom;

                    // If the global cookie property has been set, set it now.
                    if ( resp.global_cookie )
                        self.public.global_cookie = resp.global_cookie;

                    // If the exit property has been set, set the delay to 0.
                    if ( self.public.exit )
                        self.public.delay = 0;

                    // If the second property is set, check for the cookie. If it does not exist, create it and return.
                    if ( self.public.second ) {
                        var second_cookie = self.getCookie('om-second-' + self.public.optin);
                        if ( ! second_cookie ) {
                            self.createCookie('om-second-' + self.public.optin, true, self.public.expires);
                            return;
                        }
                    }

                    // If manually triggered, set some properties that go with manual triggering.
                    if ( self.public.manual ) {
                        self.public.delay  = 0;
                        self.public.exit   = false;
                        self.public.second = false;
                    }

                    // If we have been given fonts to load from Google, insert them in the head now.
                    if ( resp.fonts ) {
                        var wf    = document.createElement('script');
                        wf.src    = '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js';
                        wf.onload = wf.onreadystatechange = function(){
                            var s = this.readyState;
                            if (s) if (s != 'complete') if (s != 'loaded') return;
                            try {
                                // Load the fonts from Google.
                                self.doFonts(resp.fonts);

                                // Create output based on the type of optin received (and wait until fonts are loaded).
                                self.doOptin($);
                            } catch(e){}
                        };

                        // Attempt to append it to the <head>, otherwise append to the document.
                        (document.getElementsByTagName('head')[0] || document.documentElement).appendChild(wf);
                    } else {
                        // Create output based on the type of optin received.
                        self.doOptin($);
                    }
                },
                error: function(a, b, c){ return; }
            });
        });
    },

    // Handle loading all Google-related fonts.
    this.doFonts = function(fonts){
        WebFont.load({
            google: {
                families: [fonts]
            }
        });
    },

    // Do the optin output.
    this.doOptin = function($){
        // Store localized copy of our main object instance.
        var self = this;

        // Prep custom HTML optins if loaded.
        if ( self.public.custom )
            self.loadElementChange();

        // Trigger event to say OptinMonster is initialized.
        self.trigger('OptinMonsterInit');

        // Do custom jQuery handlers based on the type of optin retrieved.
        switch ( self.public.type ) {
            case 'lightbox' :
                self.doLightbox($);
                return;
            case 'footer' :
                self.doFooter($);
                return;
            case 'slide' :
                self.doSlide($);
                return;
        }
    },

    this.doLightbox = function($, resp){
        // Store localized copy of our main object instance.
        var self = this;

        // Wrap everything in a timeout to catch any delays.
        setTimeout(function(){
            // Append and style the holder to the body.
            self.appendHolder($, { 'position' : 'fixed', 'z-index' : '7371832', 'top' : '0', 'left' : '0', 'zoom' : '1', 'width' : '100%', 'height' : '100%', 'margin' : '0', 'padding' : '0' });

            // Load the optin HTML into the holder.
            $('#om-' + self.public.optin).html(self.public.html);

            // If a custom HTML optin, prep the custom labels.
            if ( self.public.custom )
                self.prepareCustomOptin($);

            // Trigger event to say OptinMonster HTML has been loaded.
            self.trigger('OptinMonsterLoaded');

            // If the exit intent property is checked, load that instead.
            if ( self.public.exit ) {
                $(document).one('mouseleave', function(){
                    // Trigger event to say OptinMonster is about to open.
                    self.trigger('OptinMonsterBeforeShow');

                    // Immediately show the optin.
                    $('#om-' + self.public.optin).show().css('display', 'block');

                    // If there is any custom JS tied to an optin, do it now.
                    var om_jq_func = 'om_js_' + self.public.optin.replace('-', '_'),
                        om_js_init = typeof window[om_jq_func] == 'function' ? new window[om_jq_func] : false;

                    // If we have verified that it is a function, fire it off.
                    if ( om_js_init )
                        om_js_init.init($);

                    // Load in the placeholder polyfill.
                    self.doPlaceholder($);

                    // Show the optin.
                    $('#om-' + self.public.type + '-' + self.public.theme + '-optin').show().css('display', 'block');

                    // Set the proper size of the input field.
                    if ( 'case-study-theme' == self.public.theme ) {
                        $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()+12) + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });
                    } else {
                        $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight() + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });
                    }

                    // Trigger event to say OptinMonster is now open.
                    self.trigger('OptinMonsterOnShow');
                });
            } else {
                // Trigger event to say OptinMonster is about to open.
                self.trigger('OptinMonsterBeforeShow');

                // Display the optin.
                $('#om-' + self.public.optin).fadeIn(300, function(){
                    // If there is any custom JS tied to an optin, do it now.
                    var om_jq_func = 'om_js_' + self.public.optin.replace('-', '_'),
                        om_js_init = typeof window[om_jq_func] == 'function' ? new window[om_jq_func] : false;

                    // If we have verified that it is a function, fire it off.
                    if ( om_js_init )
                        om_js_init.init($);

                    // Load in the placeholder polyfill.
                    self.doPlaceholder($);

                    // Show the optin.
                    $('#om-' + self.public.type + '-' + self.public.theme + '-optin').show().css('display', 'block');

                    // Set the proper size of the input field.
                    if ( 'case-study-theme' == self.public.theme ) {
                        $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()+12) + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });
                    } else {
                        $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight() + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });
                    }

                    // Trigger event to say OptinMonster is now open.
                    self.trigger('OptinMonsterOnShow');
                });
            }

            // Close out the optin.
            $(document).on('click.closeLightboxOptin', '#om-' + self.public.optin + ' #om-close', function(e){
                e.preventDefault();

                // Trigger event to say OptinMonster is about to close.
                self.trigger('OptinMonsterBeforeClose');

                $('#om-' + self.public.optin).fadeOut(300, function(){
                    self.createCookie('om-' + self.public.optin, true, self.public.expires);

                    // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
                    if ( self.public._optin )
                        self.createCookie('om-' + self.public._optin, true, self.public.expires);

                    // Trigger event to say OptinMonster has closed.
                    self.trigger('OptinMonsterOnClose');
                });
            });

            // Process the optin submission.
            $(document).on('click.doLightboxOptin', '#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit', function(e){
                // Prevent the default from occurring.
                e.preventDefault();

                // Trigger event to say OptinMonster is about to process an optin.
                self.trigger('OptinMonsterBeforeOptin');

                // Handle the action.
                self.doOptinAction($, e.target);
            });

            // Process custom HTML optins a little differently.
            if ( self.public.custom ) {
                $(document).on('submit.doCustomLightboxOptin', '#om-' + self.public.optin + '-custom-form', function(e){
                    // Trigger event to say OptinMonster is about to process an optin.
                    self.trigger('OptinMonsterBeforeOptin');

                    // Set cookies.
                    self.createCookie('om-' + self.public.optin, true, self.public.expires);

                    // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
                    if ( self.public._optin )
                        self.createCookie('om-' + self.public._optin, true, self.public.expires);

                    // If the global setting is populated, set a global cookie.
                    if ( self.public.global_cookie )
                        self.createCookie('om-global-cookie');

                    // Set flag to true and capture the data.
                    $('#om-' + self.public.optin + ' input[type=submit], #om-' + self.public.optin + ' input[type=submit]').attr('disabled', 'disabled').css({ 'opacity' : '.5', 'cursor' : 'progress' });

                    // Trigger event to say OptinMonster is about to submit a custom form.
                    self.trigger('OptinMonsterCustomFormSubmitted');

                    // Run a custom ajax request to capture the conversion for custom HTML forms.
                    self.doCustomOptin($, self.public.optin, e.target);

                    // Submit the form.
                    return true;
                });
            }
        }, self.public.delay);
    },

    this.doFooter = function($, resp){
        // Store localized copy of our main object instance.
        var self  = this,
            optin = $('#om-' + self.public.optin);

        // Wrap everything in a timeout to catch any delays.
        setTimeout(function(){
            // Append and style the holder to the body.
            self.appendHolder($, { 'position' : 'fixed', 'z-index' : '7371832', 'bottom' : '0', 'left' : '0', 'zoom' : '1', 'width' : '100%', 'margin' : '0', 'padding' : '0' });

            // Load the optin HTML into the holder.
            optin.html(self.public.html);

            // If a custom HTML optin, prep the custom labels.
            if ( self.public.custom )
                self.prepareCustomOptin($);

            // Trigger event to say OptinMonster is loaded.
            self.trigger('OptinMonsterLoaded');

            // If the exit intent property is checked, load that instead.
            if ( self.public.exit ) {
                $(document).one('mouseleave', function(){
                    // Trigger event to say OptinMonster is about to open.
                    self.trigger('OptinMonsterBeforeShow');

                    // Show the optin.
                    var holder = $('#om-' + self.public.type + '-' + self.public.theme + '-optin');
                    holder.show().css('display', 'block');

                    // Save the height of the optin bar.
                    var optin_height = optin.height();

                    // Display the optin.
                    optin.show().css('display', 'block');

                    // If there is any custom JS tied to an optin, do it now.
                    var om_jq_func = 'om_js_' + self.public.optin.replace('-', '_'),
                        om_js_init = typeof window[om_jq_func] == 'function' ? new window[om_jq_func] : false;

                    // If we have verified that it is a function, fire it off.
                    if ( om_js_init )
                        om_js_init.init($);

                    // Load in the placeholder polyfill.
                    self.doPlaceholder($);

                    // Set the proper size of the input field.
                    $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight() + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });

                    // Trigger event to say OptinMonster is now open.
                    self.trigger('OptinMonsterOnShow');
                });
            } else {
                // Trigger event to say OptinMonster is about to open.
                self.trigger('OptinMonsterBeforeShow');

                // Show the optin.
                var holder = $('#om-' + self.public.type + '-' + self.public.theme + '-optin');
                holder.show().css('display', 'block');

                // Save the height of the optin bar.
                var optin_height = optin.height();

                // Display the optin.
                optin.css('bottom', '-' + optin_height + 'px').show().animate({ 'bottom' : '0' }, 300, function(){
                    // If there is any custom JS tied to an optin, do it now.
                    var om_jq_func = 'om_js_' + self.public.optin.replace('-', '_'),
                        om_js_init = typeof window[om_jq_func] == 'function' ? new window[om_jq_func] : false;

                    // If we have verified that it is a function, fire it off.
                    if ( om_js_init )
                        om_js_init.init($);

                    // Load in the placeholder polyfill.
                    self.doPlaceholder($);

                    // Set the proper size of the input field.
                    $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight() + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });

                    // Trigger event to say OptinMonster is now open.
                    self.trigger('OptinMonsterOnShow');
                });
            }

            // Close out the optin.
            $(document).on('click.closeFooterOptin', '#om-' + self.public.optin + ' #om-close', function(e){
                e.preventDefault();

                // Trigger event to say OptinMonster is about to close.
                self.trigger('OptinMonsterBeforeClose');

                optin.animate({ 'bottom' : '-' + optin_height + 'px' }, 300, function(){
                    self.createCookie('om-' + self.public.optin, true, self.public.expires);

                    // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
                    if ( self.public._optin )
                        self.createCookie('om-' + self.public._optin, true, self.public.expires);

                    // Trigger event to say OptinMonster has closed.
                    self.trigger('OptinMonsterOnClose');
                });
            });

            // Process the optin submission.
            $(document).on('click.doFooterOptin', '#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit', function(e){
                // Prevent the default from occurring.
                e.preventDefault();

                // Trigger event to say OptinMonster is about to process an optin.
                self.trigger('OptinMonsterBeforeOptin');

                // Handle the action.
                self.doOptinAction($, e.target);
            });

            // Process custom HTML optins a little differently.
            if ( self.public.custom ) {
                $(document).on('submit.doCustomFooterOptin', '#om-' + self.public.optin + '-custom-form', function(e){
                    // Trigger event to say OptinMonster is about to process an optin.
                    self.trigger('OptinMonsterBeforeOptin');

                    // Set cookies.
                    self.createCookie('om-' + self.public.optin, true, self.public.expires);

                    // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
                    if ( self.public._optin )
                        self.createCookie('om-' + self.public._optin, true, self.public.expires);

                    // If the global setting is populated, set a global cookie.
                    if ( self.public.global_cookie )
                        self.createCookie('om-global-cookie');

                    // Set flag to true and capture the data.
                    $('#om-' + self.public.optin + ' input[type=submit], #om-' + self.public.optin + ' input[type=submit]').attr('disabled', 'disabled').css({ 'opacity' : '.5', 'cursor' : 'progress' });

                    // Trigger event to say OptinMonster is about to submit a custom form.
                    self.trigger('OptinMonsterCustomFormSubmitted');

                    // Run a custom ajax request to capture the conversion for custom HTML forms.
                    self.doCustomOptin($, self.public.optin, e.target);

                    // Submit the form.
                    return true;
                });
            }
        }, self.public.delay);
    },

    this.doSlide = function($, resp){
        // Store localized copy of our main object instance.
        var self   = this,
            optin  = $('#om-' + self.public.optin),
            opened = false;

        // Append and style the holder to the body.
        self.appendHolder($, {});

        // Load the optin HTML into the holder.
        optin.html(self.public.html);

        // If a custom HTML optin, prep the custom labels.
            if ( self.public.custom )
                self.prepareCustomOptin($);

        // Trigger event to say OptinMonster is loaded.
        self.trigger('OptinMonsterLoaded');

        // If the exit intent property is checked, load that instead.
        if ( self.public.exit ) {
            // Trigger event to say OptinMonster is about to open.
            self.trigger('OptinMonsterBeforeShow');

            // Show the optin.
            var holder = $('#om-' + self.public.type + '-' + self.public.theme + '-optin');
            holder.show().css('display', 'block');

            // Save the height of the optin bar.
            var optin_height = optin.height();

            // Display the optin.
            optin.css('bottom', '-' + optin_height + 'px').show().animate({ 'bottom' : '0' }, 300, function(){
                // If there is any custom JS tied to an optin, do it now.
                var om_jq_func = 'om_js_' + self.public.optin.replace('-', '_'),
                    om_js_init = typeof window[om_jq_func] == 'function' ? new window[om_jq_func] : false;

                // If we have verified that it is a function, fire it off.
                if ( om_js_init )
                    om_js_init.init($);

                // Load in the placeholder polyfill.
                self.doPlaceholder($);

                // Trigger event to say OptinMonster is now open.
                self.trigger('OptinMonsterOnShow');
            });

            // Force the slide in to pop out on exit.
            $(document).one('mouseleave', function(){
                // If no cookies have been set yet for this optin, set delay to open the popup.
                if ( ! self.getCookie('om-' + self.public.optin) ) {
                    holder.removeClass('om-slide-closed').addClass('om-slide-open');

                    // Set the proper line height of the closing X.
                    $('#om-' + self.public.type + '-' + self.public.theme + '-optin .om-slide-close-holder span').css({ 'line-height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-title-open').outerHeight() + 'px' });

                    // Set the proper size of the input field.
                    $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight() + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });

                    // Trigger event to say OptinMonster is now open.
                    self.trigger('OptinMonsterOnShow');
                }
            });
        } else {
            // Trigger event to say OptinMonster is about to open.
            self.trigger('OptinMonsterBeforeShow');

            // Show the optin.
            var holder = $('#om-' + self.public.type + '-' + self.public.theme + '-optin');
            holder.show().css('display', 'block');

            // Save the height of the optin bar.
            var optin_height = optin.height();

            // Display the optin.
            optin.css('bottom', '-' + optin_height + 'px').show().animate({ 'bottom' : '0' }, 300, function(){
                // If there is any custom JS tied to an optin, do it now.
                var om_jq_func = 'om_js_' + self.public.optin.replace('-', '_'),
                    om_js_init = typeof window[om_jq_func] == 'function' ? new window[om_jq_func] : false;

                // If we have verified that it is a function, fire it off.
                if ( om_js_init )
                    om_js_init.init($);

                // Load in the placeholder polyfill.
                self.doPlaceholder($);

                // If no cookies have been set yet for this optin, set delay to open the popup.
                if ( ! self.getCookie('om-' + self.public.optin) ) {
                    setTimeout(function(){
                        // If the slide has already been opened, return early.
                        if ( opened ) return;

                        holder.removeClass('om-slide-closed').addClass('om-slide-open');

                        // Set the proper line height of the closing X.
                        $('#om-' + self.public.type + '-' + self.public.theme + '-optin .om-slide-close-holder span').css({ 'line-height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-title-open').outerHeight() + 'px' });

                        // Set the proper size of the input field.
                        $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight() + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });

                        // Trigger event to say OptinMonster is now open.
                        self.trigger('OptinMonsterOnShow');
                    }, self.public.delay);
                }
            });
        }

        // Close out the optin.
        $(document).on('click.closeSlideOptin', '#om-' + self.public.optin + ' .om-slide-close-holder', function(e){
            e.preventDefault();

            // Trigger event to say OptinMonster is about to close.
            self.trigger('OptinMonsterBeforeClose');

            holder.removeClass('om-slide-open').addClass('om-slide-closed');

            // If no cookie exists, set it and set our opened flag to true.
            if ( ! self.getCookie('om-' + self.public.optin) ) {
                self.createCookie('om-' + self.public.optin, true, self.public.expires);

                // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
                if ( self.public._optin )
                    self.createCookie('om-' + self.public._optin, true, self.public.expires);

                opened = true;

                // Trigger event to say OptinMonster is now closed.
                self.trigger('OptinMonsterOnClose');
            }
        });

        // Open the optin.
        $(document).on('click.openSlideOptin', '#om-' + self.public.optin + ' .om-slide-open-holder', function(e){
            e.preventDefault();

            // Trigger event to say OptinMonster is about to open.
            self.trigger('OptinMonsterBeforeShow');

            holder.removeClass('om-slide-closed').addClass('om-slide-open');

            // Set the proper line height of the closing X.
            $('#om-' + self.public.type + '-' + self.public.theme + '-optin .om-slide-close-holder span').css({ 'line-height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-title-open').outerHeight() + 'px' });

            // Set the proper size of the input field.
            $('#om-' + self.public.type + '-' + self.public.theme + '-optin-submit').css({ 'height' : $('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight() + 'px', 'line-height': ($('#om-' + self.public.type + '-' + self.public.theme + '-optin-email').outerHeight()-10) + 'px' });

            // If no cookie exists, set it and set our opened flag to true.
            if ( ! self.getCookie('om-' + self.public.optin) ) {
                self.createCookie('om-' + self.public.optin, true, self.public.expires);
                opened = true;
            }

            // Trigger event to say OptinMonster is now open.
            self.trigger('OptinMonsterOnShow');
        });

        // Process the optin submission.
        $(document).on('click.doSlideOptin', '#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit', function(e){
            // Prevent the default from occurring.
            e.preventDefault();

            // Trigger event to say OptinMonster is about to process an optin.
            self.trigger('OptinMonsterBeforeOptin');

            // Handle the action.
            self.doOptinAction($, e.target);
        });

        // Process custom HTML optins a little differently.
        if ( self.public.custom ) {
            $(document).on('submit.doCustomLightboxOptin', '#om-' + self.public.optin + '-custom-form', function(e){
                // Trigger event to say OptinMonster is about to process an optin.
                self.trigger('OptinMonsterBeforeOptin');

                // Set cookies.
                self.createCookie('om-' + self.public.optin, true, self.public.expires);

                // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
                if ( self.public._optin )
                    self.createCookie('om-' + self.public._optin, true, self.public.expires);

                // If the global setting is populated, set a global cookie.
                if ( self.public.global_cookie )
                    self.createCookie('om-global-cookie');

                // Set flag to true and capture the data.
                $('#om-' + self.public.optin + ' input[type=submit], #om-' + self.public.optin + ' input[type=submit]').attr('disabled', 'disabled').css({ 'opacity' : '.5', 'cursor' : 'progress' });

                // Trigger event to say OptinMonster is about to submit a custom form.
                self.trigger('OptinMonsterCustomFormSubmitted');

                // Run a custom ajax request to capture the conversion for custom HTML forms.
                self.doCustomOptin($, self.public.optin, e.target);

                // Submit the form.
                return true;
            });
        }
    },

    // Appends the optin holder to the <body> tag on the page.
    this.appendHolder = function($, styles){
        $('#om-' + this.public.optin).css(styles).appendTo('body');
    },

    // Ensure that the placeholder polyfill is added.
    this.doPlaceholder = function($){
        // Store localized copy of our main object instance.
        var self   = this,
            inputs = $('#om-' + self.public.optin + ' input');

        if ( inputs.length > 0 ) {
            inputs.each(function(){
                if ( $.fn.placeholder )
                    $(this).placeholder();
            });
        }

        // Trigger event to say OptinMonster placeholders are set.
        self.trigger('OptinMonsterPlaceholdersDone');
    },

    // Prepare custom optin output.
    this.prepareCustomOptin = function($){
        // Store localized copy of our main object instance.
        var self   = this,
            inputs = $('#om-' + self.public.optin + ' input[data-om-render=label]');

        if ( inputs.length > 0 ) {
            inputs.each(function(){
                if ( $.fn.changeElementType )
                    $(this).changeElementType('label');
            });

            // Now convert the labels to their proper format.
            $('#om-' + self.public.optin + ' label[data-om-render=label]').each(function(){
                $(this).text($(this).attr('value')).removeAttr('value type');
            });
        }

        // Trigger event to say OptinMonster has finished preparing custom optins.
        self.trigger('OptinMonsterCustomDone');
    },

    // Polling helper for executing at certain intervals.
    this.poll = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })(),

    // Retrieve the proper URL protocol.
    this.getUrlProtocol = function(){
        return 'https:' == document.location.protocol ? 'https://' : 'http://';
    },

    // Verifies that all fields are populated correctly before proceeding to process the optin.
    this.verifyFields = function($, optin, type, theme){
        // Prepare variables.
        var self  = this,
            name  = $('#om-' + type + '-' + theme + '-optin-name'),
            email = $('#om-' + type + '-' + theme + '-optin-email')
            ret   = {};

        // If the name field is present, verify that it is filled out.
        if ( name && name.length > 0 ) {
            if ( name.val().length == 0 ) {
                // Trigger event to say OptinMonster has a name error.
                self.trigger('OptinMonsterNameError');

                ret['error'] = 'Please enter your name.';
                return ret;
            }
        }

        // If the email field is present, verify that the email address is correct.
        if ( email && email.length > 0 ) {
            if ( ! self.isValidEmail(email.val()) ) {
                // Trigger event to say OptinMonster has an email error.
                self.trigger('OptinMonsterEmailError');

                ret['error'] = 'Please enter a valid email address.';
                return ret;
            }
        }

        // If we have reached this point, return with our email address in tact (and possibly a name as well).
        ret['email'] = email.val();
        if ( name && name.length > 0 )
            ret['name'] = name.val();
        else
            ret['name'] = false;

        return ret;
    },

    // Handle the click event to process the optin action.
    this.doOptinAction = function($, target){
        var self = this;

        // Disable submit button and add opacity layer to represent action occurring.
        $('.om-error-message').remove();
        $('#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit').attr('disabled', 'disabled').css({ 'opacity' : '.5', 'cursor' : 'progress' });

        // If this is a custom form, do the optin and invoke the manual submission.
        if ( self.public.custom ) {
            // Trigger event to say OptinMonster is about to submit a custom form.
            self.trigger('OptinMonsterCustomFormSubmitted');

            // Run a custom ajax request to capture the conversion for custom HTML forms.
            self.doCustomOptin($, self.public.optin, target);

            // Return early since this a custom HTML form.
            return;
        }

        // Verify name and email fields, if they exist, are populated correctly.
        var verify = self.verifyFields($, self.public.optin, self.public.type, self.public.theme);
        if ( verify && verify.error ) {
            $(target).after('<p class="om-error-message" style="color:#a41629;display:block;margin:10px 0 0;font-weight:bold;">' + verify.error + '</p>');
            $('#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit').removeAttr('disabled').css({ 'opacity' : '1', 'cursor' : 'pointer' });
            return;
        } else {
            // Trigger event to say OptinMonster is about to submit a form.
            self.trigger('OptinMonsterFormSubmitted');

            // Everything is good - let's process the optin.
            self.doEmailOptin($, verify.email, verify.name, self.public.optin, target);
        }
    },

    // Tests the validity of an email address entered by the user.
    this.isValidEmail = function(email){
        return (new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i)).test(email);
    },

    this.createCookie = function(name, value, days){
        if ( days ) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            var expires = "; expires=" + date.toGMTString();
        }
        else var expires = "";
        document.cookie = name + "=" + value + expires + "; path=/";
    },

    this.getCookie = function(name){
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    },

    this.removeCookie = function(name){
        this.createCookie(name, "", -1);
    },

    // Generic logging utility.
    this.log = function(text){
        (typeof console === 'object') ? console.log(text) : '';
    },

    // Generic event triggering utility for OptinMonster.
    this.trigger = function(event_name){
        var self = this;
        this.public.ejQuery(document).trigger(event_name, self.public);
    },

    // The main API for processing conversions for custom HTML forms.
    this.doCustomOptin = function($, optin, target){
        // Prepare variables.
        var self = this;

        // Request our optin data via JSONP from the info provided.
        $.ajax({ url: self.public.ajax, data: { action: 'do_optinmonster_custom', optin: self.public.optin, optin_id: self.public.id, referer: window.location.href, user_agent: navigator.userAgent }, cache: false, async: true, type: 'POST', dataType: 'json', timeout: 10000,
            success: function(resp){
                // Trigger event to say OptinMonster has processed the optin.
                self.trigger('OptinMonsterOptinSuccess');

                // If a redirect is specified, redirect to the specified page.
                if ( resp.redirect && resp.redirect.length > 0 ) {
                    // Trigger event to say OptinMonster is about to redirect to the success page.
                    self.trigger('OptinMonsterOnRedirect');

                    window.location.href = resp.redirect;
                } else {
                    // Hide the optin.
                    $('#om-' + self.public.optin).hide();
                }
            },
            error: function(a, b, c){
                // Trigger event to say OptinMonster has encountered an error processing the optin.
                self.trigger('OptinMonsterOptinError');

                // If a redirect is specified, redirect to the specified page.
                if ( resp.redirect && resp.redirect.length > 0 ) {
                    // Trigger event to say OptinMonster is about to redirect to the success page.
                    self.trigger('OptinMonsterOnRedirect');

                    window.location.href = resp.redirect;
                } else {
                    // Hide the optin.
                    $('#om-' + self.public.optin).hide();
                }
            }
        });
    },

    // The main API for signing up the users.
    this.doEmailOptin = function($, email, name, optin, target){
        // Prepare variables.
        var self = this;

        // Request our optin data via JSONP from the info provided.
        $.ajax({ url: self.public.ajax, data: { action: 'do_optinmonster', email: email, name: name, optin: self.public.optin, optin_id: self.public.id, referer: window.location.href, user_agent: navigator.userAgent }, cache: false, async: true, type: 'POST', dataType: 'json', timeout: 10000,
            success: function(resp){
                // If for some reason we do not receive a response or the response is empty, return with an error.
                if ( ! resp || $.isEmptyObject(resp) ) {
                    // Trigger event to say OptinMonster has encountered an error processing the optin.
                    self.trigger('OptinMonsterOptinError');

                    $(target).after('<p class="om-error-message" style="color:#a41629;display:block;margin:10px 0 0;font-weight:bold;">An unknown error occurred. Please try again.</p>');
                    $('#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit').removeAttr('disabled').css({ 'opacity' : '1', 'cursor' : 'pointer' });
                    return;
                }

                // If we receive an error message, return with the error message from the API.
                if ( resp && resp.error ) {
                    // Trigger event to say OptinMonster has encountered an error processing the optin.
                    self.trigger('OptinMonsterOptinError');

                    $(target).after('<p class="om-error-message" style="color:#a41629;display:block;margin:10px 0 0;font-weight:bold;">' + resp.error + '</p>');
                    $('#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit').removeAttr('disabled').css({ 'opacity' : '1', 'cursor' : 'pointer' });
                    return;
                }

                // If we make it here, success! We have successfully signed the user up. Set cookie and hide optin.
                self.createCookie('om-' + self.public.optin, true, self.public.expires);

                // If using a clone, make sure the clone has a cookie as well to prevent it from displaying.
                if ( self.public._optin )
                    self.createCookie('om-' + self.public._optin, true, self.public.expires);

                // If the global setting is populated, set a global cookie.
                if ( self.public.global_cookie )
                    self.createCookie('om-global-cookie');

                // Set the success message.
                $('#om-' + self.public.optin + ' #om-' + self.public.type + '-' + self.public.theme + '-optin-submit').val('Success!');

                // Trigger event to say OptinMonster has processed the optin successfully.
                self.trigger('OptinMonsterOptinSuccess');

                // If a redirect is specified, redirect to the specified page.
                if ( resp.redirect && resp.redirect.length > 0 ) {
                    // Trigger event to say OptinMonster is about to redirect to the success page.
                    self.trigger('OptinMonsterOnRedirect');

                    window.location.href = resp.redirect;
                } else {
                    setTimeout(function(){
                        $('#om-' + self.public.optin).hide();

                        // Trigger event to say OptinMonster has processed the optin successfully and has closed.
                        self.trigger('OptinMonsterOptinSuccessClose');
                    }, 1000);
                }
            },
            error: function(a, b, c){
                // Trigger event to say OptinMonster has encountered an error processing the optin.
                self.trigger('OptinMonsterOptinError');
                return;
            }
        });
    },

    // Loads the jQuery placeholder polyfill.
    this.loadPlaceholder = function(){
        var self = this;

        /*! http://mths.be/placeholder v2.0.7 by @mathias */
        ;(function(h,j,e){var a="placeholder" in j.createElement("input");var f="placeholder" in j.createElement("textarea");var k=e.fn;var d=e.valHooks;var b=e.propHooks;var m;var l;if(a&&f){l=k.placeholder=function(){return this};l.input=l.textarea=true}else{l=k.placeholder=function(){var n=this;n.filter((a?"textarea":":input")+"[placeholder]").not(".placeholder").bind({"focus.placeholder":c,"blur.placeholder":g}).data("placeholder-enabled",true).trigger("blur.placeholder");return n};l.input=a;l.textarea=f;m={get:function(o){var n=e(o);var p=n.data("placeholder-password");if(p){return p[0].value}return n.data("placeholder-enabled")&&n.hasClass("placeholder")?"":o.value},set:function(o,q){var n=e(o);var p=n.data("placeholder-password");if(p){return p[0].value=q}if(!n.data("placeholder-enabled")){return o.value=q}if(q==""){o.value=q;if(o!=j.activeElement){g.call(o)}}else{if(n.hasClass("placeholder")){c.call(o,true,q)||(o.value=q)}else{o.value=q}}return n}};if(!a){d.input=m;b.value=m}if(!f){d.textarea=m;b.value=m}e(function(){e(j).delegate("form","submit.placeholder",function(){var n=e(".placeholder",this).each(c);setTimeout(function(){n.each(g)},10)})});e(h).bind("beforeunload.placeholder",function(){e(".placeholder").each(function(){this.value=""})})}function i(o){var n={};var p=/^jQuery\d+$/;e.each(o.attributes,function(r,q){if(q.specified&&!p.test(q.name)){n[q.name]=q.value}});return n}function c(o,p){var n=this;var q=e(n);if(n.value==q.attr("placeholder")&&q.hasClass("placeholder")){if(q.data("placeholder-password")){q=q.hide().next().show().attr("id",q.removeAttr("id").data("placeholder-id"));if(o===true){return q[0].value=p}q.focus()}else{n.value="";q.removeClass("placeholder");n==j.activeElement&&n.select()}}}function g(){var r;var n=this;var q=e(n);var p=this.id;if(n.value==""){if(n.type=="password"){if(!q.data("placeholder-textinput")){try{r=q.clone().attr({type:"text"})}catch(o){r=e("<input>").attr(e.extend(i(this),{type:"text"}))}r.removeAttr("name").data({"placeholder-password":q,"placeholder-id":p}).bind("focus.placeholder",c);q.data({"placeholder-textinput":r,"placeholder-id":p}).before(r)}q=q.removeAttr("id").hide().prev().attr("id",p).show()}q.addClass("placeholder");q[0].value=q.attr("placeholder")}else{q.removeClass("placeholder")}}}(this,document,self.public.jQuery));
    },

    // Loads the element changer.
    this.loadElementChange = function(){
        var self = this;

        ;(function(a){a.fn.changeElementType=function(c){var b={};a.each(this[0].attributes,function(e,d){b[d.nodeName]=d.nodeValue});this.replaceWith(function(){return a("<"+c+"/>",b).append(a(this).contents())})}})(self.public.jQuery);
    }

    // Checks to ensure cookies are enabled for the current visitor.
    this.cookiesEnabled = function(){
        var cookieEnabled = (navigator.cookieEnabled) ? true : false;

        if (typeof navigator.cookieEnabled == "undefined" && !cookieEnabled)
        {
            document.cookie = "testcookie";
            cookieEnabled   = (document.cookie.indexOf("testcookie") != -1) ? true : false;
        }
        return (cookieEnabled);
    },

    // Mobile device check.
    this.isMobile = function(){
        var check = false;
        (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    }
}