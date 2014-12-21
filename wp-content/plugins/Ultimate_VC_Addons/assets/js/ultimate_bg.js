(function ( jQuery ) {
	
	function vc_viewport_video()
	{
		jQuery('.enable-on-viewport').each(function(index, element) {
            var is_on_viewport = jQuery(this).isVdoOnScreen();
			if(jQuery(this).hasClass('hosted-video') && (!jQuery(this).hasClass('override-controls')))
			{
				if(is_on_viewport)
				{
					jQuery(this)[0].play();
					jQuery(this).parent().parent().parent().find('.video-controls').attr('data-action','play');
					jQuery(this).parent().parent().parent().find('.video-controls').html('<i class="wooicon-pause"></i>');
				}
				else
				{
					jQuery(this)[0].pause();
					jQuery(this).parent().parent().parent().find('.video-controls').attr('data-action','pause');
					jQuery(this).parent().parent().parent().find('.video-controls').html('<i class="wooicon-play"></i>');
				}
			}
        });
	}
	
	jQuery(window).scroll(function(){
		vc_viewport_video();
	});
	
	jQuery.fn.isVdoOnScreen = function(){
		var win =jQuery(window);
		
		var viewport = {
			top : win.scrollTop(),
			left : win.scrollLeft()
		};
		viewport.right = viewport.left + win.width();
		viewport.bottom = viewport.top + win.height()-200;
		
		var bounds = this.offset();
		bounds.right = bounds.left + this.outerWidth();
		bounds.bottom = bounds.top + this.outerHeight()-300;
		
		return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
	};

	jQuery.fn.ultimate_video_bg = function(option) {
		jQuery(this).each(function(){
			var selector =jQuery(this);
			var vdo = selector.data('ultimate-video');
			var vdo2 = selector.data('ultimate-video2');
			var muted =selector.data('ultimate-video-muted');
			var loop =selector.data('ultimate-video-loop');
			var autoplay =selector.data('ultimate-video-autoplay');
			var poster =selector.data('ultimate-video-poster');
			var ride = selector.data('bg-override');
			var start = selector.data('start-time');
			var stop = selector.data('stop-time');
			var anim_style= selector.data('upb-bg-animation');
			var overlay_color = selector.data('upb-overlay-color');
			var viewport_vdo = selector.data('viewport-video');
			var controls = selector.data('controls');
			var controls_color = selector.data('controls-color');
			var fadeout = selector.data('fadeout');
			var fadeout_percentage = selector.data('fadeout-percentage');
			var parallax_content = selector.data('parallax-content');
			var parallax_content_sense = selector.data('parallax-content-sense');
			var disble_mobile = selector.data('row-effect-mobile-disable');
						
			if( overlay_color != ''){
				overlay_color = '<div class="upb_bg_overlay" style="background-color:'+overlay_color+'"></div>';
			}
			else{
				//console.warn("Overlay color not selected");
			}
			if(stop!=0){
				stop = stop;
			}else{
				stop ='';
			}
			var parent = selector.prev();
			selector.remove();
			selector = parent;
			
			var vd_html = selector.html();
			selector.addClass('upb_video_class');
			selector.attr('data-row-effect-mobile-disable',disble_mobile);
			if(fadeout == 'fadeout_row_value')
			{
				selector.addClass('vc-row-fade');
				selector.data('fadeout-percentage',fadeout_percentage);
			}
			if(parallax_content == 'parallax_content_value')
			{
				selector.addClass('vc-row-translate');
				selector.attr('data-parallax-content-sense', parallax_content_sense);
			}
			selector.attr('data-upb_br_animation',anim_style);
			if(vdo){
				if(vdo.indexOf('youtube.com')!=-1){
					option='youtube';
				}
				else if (vdo.indexOf('vimeo.com')!=-1){
					option='vimeo'
				}
			}
			
			var control_html = '';
			if(controls == 'display_control'){
				control_html = '<span class="video-controls" data-action="play" style="color:'+controls_color+'"><i class="wooicon-pause"></i></span>';
			}
			
			if(option=='youtube' || option=='vimeo'){
				selector.html('<div class="upb_video-wrapper"><div class="upb_video-bg utube" data-bg-override="'+ride+'"></div></div><div class="upb_video-text-wrapper"><div class="upb_video-text"></div></div>');
			}else{
				selector.html(' <div class="upb_video-wrapper"><div class="upb_video-bg" data-bg-override="'+ride+'"><video class="upb_video-src"></video>'+control_html+overlay_color+'</div></div><div class="upb_video-text-wrapper"><div class="upb_video-text"></div></div>');
				
				/*jQuery("#myvdo").on('touchstart', function(e) {
					var videoElement = document.getElementById("myvdo");
					videoElement.play(); 
				});*/
			}
			
			
			
			selector.find('.upb_video-text').html(vd_html);
			if(option=='youtube'){
				vdo = vdo.substring((vdo.indexOf('watch?v='))+8,(vdo.indexOf('watch?v='))+19);
				var content = selector.find('.upb_video-bg');
				if(loop=='loop') loop=true;
				if(muted=='muted') muted=true;
				//alert(loop+' '+muted+' '+vdo);
				content.attr('data-vdo',vdo);content.attr('data-loop',loop);content.attr('data-poster',poster);
				content.attr('data-muted',muted);content.attr('data-start',start);content.attr('data-stop',stop);
				
				if(viewport_vdo === true)
				{
					content.addClass('enable-on-viewport');
					content.addClass('youtube-video');
					vc_viewport_video();
				}
				
				//content.html('<iframe class="upb_utube_iframe" frameborder="0" allowfullscreen="1" src="https://www.youtube.com/embed/'+vdo+'?autoplay=1&loop=1&controls=0&disablekb=1&enablejsapi=1&fs=0&iv_load_policy =3&modestbranding=1&rel=0&showinfo=0&wmode=transparent&amp;start='+start+'&amp;'+stop+'" width="900" height="1600"></iframe>')
			}else if(option=='vimeo'){
				vdo = vdo.substring((vdo.indexOf('vimeo.com/'))+10,(vdo.indexOf('vimeo.com/'))+18);
				var content = selector.find('.upb_video-bg');
				content.html('<iframe class="upb_vimeo_iframe" src="http://player.vimeo.com/video/'+vdo+'?portrait=0&amp;byline=0&amp;title=0&amp;badge=0&amp;loop=0&amp;autoplay=1&amp;api=1&amp;rel=0&amp;" height="1600" width="900" frameborder=""></iframe>')
				//.controls-wrapper
				//.controls
				/*jQuery(window).load(function(){
					setTimeout(function() {
						var if_co = jQuery('iframe.upb_vimeo_iframe').contents().find("#player");
						if_co.find(".controls-wrapper").css("display","none");
					}, 5000);
				})
				*/
			}
			else{
				var content = selector.find('.upb_video-src');
				
				if(! /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )
				{
					jQuery('<source/>', {
						type: 'video/mp4',
						src: vdo
					}).appendTo(content);
					//content.attr({'src':vdo});
					if(vdo2 != '')
					{
						var vdo2_type = '';
						if(vdo2.match(/.ogg/i))
							vdo2_type = 'video/ogg';
						else if(vdo2.match(/.webm/i))
							vdo2_type = 'video/webm';
						if(vdo2_type != '') 
						{
							jQuery('<source/>', {
								type: vdo2_type,
								src: vdo2
							}).appendTo(content);
						}
					}
									
					if(muted=='muted'){ content.attr({'muted':muted}); }
					if(loop=='loop'){ content.attr({'loop':loop}); }
					if(poster){ content.attr({'poster':poster}); }
					content.attr({'preload':'auto'});
					if(viewport_vdo === true)
					{
						content.addClass('enable-on-viewport');
						content.addClass('hosted-video');
						vc_viewport_video();
					}
					else
					{
						if(autoplay=='autoplay'){ content.attr({'autoplay':autoplay}); }
					}
				}
				else
				{
					if(poster != '')
						content.parent().css({'background-image':'url('+poster+')'});
					content.remove();
				}	
			}
			var resizee = function(){
				var w,h,ancenstor,al='',bl='';
				ancenstor = selector;
				selector = selector.find('.upb_video-bg');
				if(ride=='full'){
					ancenstor= jQuery('body');
				}
				if(ride=='ex-full'){
					ancenstor= jQuery('html');
				}
				if( ! isNaN(ride)){
					for(var i=0;i<ride;i++){
						if(ancenstor.prop("tagName")!='HTML'){
							ancenstor = ancenstor.parent();
						}else{
							break;
						}
					}
				}
				h = selector.parents('upb_video_class').outerHeight();
				w = ancenstor.outerWidth();
				if(ride=='browser_size'){
					h = jQuery(window).height();
					w = jQuery(window).width();
					ancenstor.css('min-height',h+'px');
				}
				selector.css({'min-height':h+'px','min-width':w+'px'});
				if(ancenstor.offset()){
					al = ancenstor.offset().left;
					if(selector.offset()){
						bl = selector.offset().left;
					}
				}
				if(al!=''){
					if(bl!=''){
						//selector.css({'left':(al-bl)+'px'});
					}
				}
			 	var width =w,
	            pWidth, // player width, to be defined
	            //height = selector.height(),
	            height = h,
	            pHeight, // player height, tbd
	        	vimeovideoplayer = selector.find('.upb_vimeo_iframe');
	        	youvideoplayer = selector.find('.upb_utube_iframe');
	        	embeddedvideoplayer = selector.find('.upb_video-src');
	        	var ratio =(16/9);
		        if(vimeovideoplayer){
			        if (width / ratio < height) { // if new video height < window height (gap underneath)
		                pWidth = Math.ceil(height * ratio); // get new player width
		                vimeovideoplayer.width(pWidth).height(height).css({left: (width - pWidth) / 2, top: 0}); // player width is greater, offset left; reset top
		            } else { // new video width < window width (gap to right)
		                pHeight = Math.ceil(width / ratio); // get new player height
		                vimeovideoplayer.width(width).height(pHeight).css({left: 0, top: (height - pHeight) / 2}); // player height is greater, offset top; reset left
		            }
		        }
		        /*if(youvideoplayer){
			        if (width / ratio < height) { // if new video height < window height (gap underneath)
		                pWidth = Math.ceil(height * ratio); // get new player width
		                youvideoplayer.width(pWidth).height(height).css({left: (width - pWidth) / 2, top: 0}); // player width is greater, offset left; reset top
		            } else { // new video width < window width (gap to right)
		                pHeight = Math.ceil(width / ratio); // get new player height
		                youvideoplayer.width(width).height(pHeight).css({left: 0, top: (height - pHeight) / 2}); // player height is greater, offset top; reset left
		            }
		        }*/
		        if(embeddedvideoplayer){
			        if (width / (16/9) < height) {
			            pWidth = Math.ceil(height * (16/9));
			            embeddedvideoplayer.width(pWidth).height(height).css({left: (width - pWidth) / 2, top: 0});
			        } else {
			            pHeight = Math.ceil(width / (16/9));
			            //youvideoplayer.width(width).height(pHeight).css({left: 0, top: (height - pHeight) / 2});
			            embeddedvideoplayer.width(width).height(pHeight).css({left: 0, top: 0});
			        }
		        }
		    }
		    resizee();
			jQuery(window).load(function(){
		    	resizee();
		    });
		    jQuery(window).resize(function(){
		    	resizee();
		    });
		})
		return this;
	}
	
	jQuery.fn.ultimate_bg_shift = function() {
		jQuery(this).each(function(){
			var selector =jQuery(this);
			var bg = selector.data('ultimate-bg');   // dep in vc v4.1
			var style = selector.data('ultimate-bg-style');
		 	/*var bg ='';
			if(style=='vcpb-fs-jquery' || style=='vcpb-mlvp-jquery'){
				bg = selector.data('ultimate-bg');
			}
			else{
				bg = selector.prev().css('background-image');
			}*/
			var bg_color = selector.prev().css('background-color');
			var rep = selector.data('bg-img-repeat');
			var size = selector.data('bg-img-size');
			var pos = selector.data('bg-img-position');
			var sense = selector.data('parallx_sense');
			var ride = selector.data('bg-override');
			var attach = selector.data('bg_img_attach');
			var anim_style= selector.data('upb-bg-animation');
			var al,bl,overlay_color='';
			var overlay_color = selector.data('upb-overlay-color');
			var fadeout = selector.data('fadeout');
			var fadeout_percentage = selector.data('fadeout-percentage');
			var parallax_content = selector.data('parallax-content');
			var parallax_content_sense = selector.data('parallax-content-sense');
			var animation = selector.data('bg-animation');
			var animation_type = selector.data('bg-animation-type');
			var animation_repeat = selector.data('animation-repeat');
			var disble_mobile = selector.data('row-effect-mobile-disable');
			var disble_mobile_img_parallax = selector.data('img-parallax-mobile-disable');
			
			if(overlay_color!=''){
				//overlay_color = '<div class="upb_bg_overlay" style="background-color:'+overlay_color+'"></div>';
			}
			selector.prev().prepend('<div class="upb_row_bg">'+overlay_color+'</div>');
			var parent = selector.prev();
			selector.remove();
			selector = parent;
			selector.attr('data-row-effect-mobile-disable',disble_mobile);
			selector.attr('data-img-parallax-mobile-disable',disble_mobile_img_parallax);
			if(fadeout == 'fadeout_row_value')
			{
				selector.addClass('vc-row-fade');
				selector.data('fadeout-percentage',fadeout_percentage);
			}
			if(parallax_content == 'parallax_content_value')
			{
				selector.addClass('vc-row-translate');
				selector.attr('data-parallax-content-sense', parallax_content_sense);
			}
			selector.css('background-image','');
			selector = selector.find('.upb_row_bg');
			selector.attr('data-upb_br_animation',anim_style);
			if(size!='automatic'){
				selector.css({'background-size':size});
			}
			else{
				selector.addClass('upb_bg_size_automatic');
			}
			selector.css({'background-repeat':rep,'background-position':pos,'background-color':bg_color});
			if(style=='vcpb-fs-jquery' || style=='vcpb-mlvp-jquery'){
				selector.attr('data-img-array',bg);
			}
			else{
				selector.css({'background-image':bg,'background-attachment':attach});
			}
			selector.attr('data-parallax_sense',sense);
			selector.attr('data-bg-override',ride);
			selector.attr('data-bg-animation',animation);
			selector.attr('data-bg-animation-type',animation_type);
			selector.attr('data-animation-repeat',animation_repeat);
			
			selector.addClass(style);
			var resize = function(){
				var w,h,ancenstor,al,bl;
				ancenstor = selector.parent();
				if(ride=='full'){
					ancenstor= jQuery('body');
					al=0;
				}
				if(ride=='ex-full'){
					ancenstor= jQuery('html');
					al=0;
				}
				if( ! isNaN(ride)){
					for(var i=0;i<ride;i++){
						if(ancenstor.prop("tagName")!='HTML'){
							ancenstor = ancenstor.parent();
						}else{
							break;
						}
					}
					al = ancenstor.offset().left;
				}
				h = selector.parent().outerHeight();
				w = ancenstor.outerWidth();
				selector.css({'min-height':h+'px','min-width':w+'px'});
				bl = selector.offset().left;
				selector.css({'left':-(Math.abs(al-bl))+'px'});
				if(ride=='browser_size'){
					selector.parent().css('min-height',jQuery(window).height()+'px');
				}
			}
			resize();
			jQuery(window).load(function(){
				resize();
			});
			jQuery(window).resize(function(){
				resize();
			});
		})
		return this;
	}
	jQuery.fn.ultimate_grad_shift = function() {
		jQuery(this).each(function(){
			var selector =jQuery(this);
			var grad = selector.data('grad');
			var parent = selector.prev();
			var last_html = parent.html();
			var ride = jQuery(this).data('bg-override');
			var overlay_color = selector.data('upb-overlay-color');
			var anim_style= selector.data('upb-bg-animation');
			var fadeout = selector.data('fadeout');
			var fadeout_percentage = selector.data('fadeout-percentage');
			var parallax_content = selector.data('parallax-content');
			var parallax_content_sense = selector.data('parallax-content-sense');
			var disble_mobile = selector.data('row-effect-mobile-disable');
			
			parent.html('');
			if(overlay_color!=''){
				overlay_color = '<div class="upb_bg_overlay" style="background-color:'+overlay_color+'"></div>';
			}
			parent.prepend('<div class="upb_row_bg">'+overlay_color+'</div><div class="upb-background-text-wrapper"><div class="upb-background-text"></div></div>');
			selector.remove();
			selector = parent;
			selector.attr('data-row-effect-mobile-disable',disble_mobile);
			if(fadeout == 'fadeout_row_value')
			{
				selector.addClass('vc-row-fade');
				selector.data('fadeout-percentage',fadeout_percentage);
			}
			if(parallax_content == 'parallax_content_value')
			{
				selector.addClass('vc-row-translate');
				selector.attr('data-parallax-content-sense', parallax_content_sense);
			}
			selector.find('.upb-background-text').html(last_html);
			selector.css('background-image','');
			selector = selector.find('.upb_row_bg');
			selector.attr('data-upb_br_animation',anim_style);
			grad = grad.replace('url(data:image/svg+xml;base64,','');
	    	var e_pos = grad.indexOf(';');
	    	grad = grad.substring(e_pos+1);
			selector.attr('style',grad);
			selector.attr('data-bg-override',ride);
			if(ride == 'browser_size')
				selector.parent().find('.upb-background-text-wrapper').addClass('full-browser-size');
		})
		return this;
	}
	jQuery.fn.ultimate_bg_color_shift = function() {
		jQuery(this).each(function(){
			var selector = jQuery(this);
			var parent = selector.prev();
			var last_html = parent.html();
			var ride = jQuery(this).data('bg-override');
			var bg_color = jQuery(this).data('bg-color');
			var fadeout = selector.data('fadeout');
			var fadeout_percentage = selector.data('fadeout-percentage');
			var parallax_content = selector.data('parallax-content');
			var parallax_content_sense = selector.data('parallax-content-sense');
			var disble_mobile = selector.data('row-effect-mobile-disable');
			
			
			if(ride == "browser_size")
			{
				parent.html('');
				var brw_text_wrapper = '<div class="upb-background-text-wrapper"><div class="upb-background-text"></div></div>';
			}
			else
				var brw_text_wrapper = '';
			parent.prepend('<div class="upb_row_bg"></div>'+brw_text_wrapper);
			selector.remove();
			selector = parent;
			selector.attr('data-row-effect-mobile-disable',disble_mobile);
			if(fadeout == 'fadeout_row_value')
			{
				selector.addClass('vc-row-fade');
				selector.data('fadeout-percentage',fadeout_percentage);
			}
			if(parallax_content == 'parallax_content_value')
			{
				selector.addClass('vc-row-translate');
				selector.attr('data-parallax-content-sense', parallax_content_sense);
			}
			if(ride == "browser_size")
				selector.find('.upb-background-text').html(last_html);
			selector.css('background-image','');
			selector = selector.find('.upb_row_bg');
			selector.css({'background':bg_color});
			selector.attr('data-bg-override',ride);
			if(ride == 'browser_size')
				selector.parent().find('.upb-background-text-wrapper').addClass('full-browser-size');
		})
		return this;
	}
	jQuery.fn.ultimate_parallax_animation = function(applyTo) {
		var windowHeight = jQuery(window).height();
		var getHeight = function(obj) {
				return obj.height();
			};
		var $this = jQuery(this);
		var prev_pos = jQuery(window).scrollTop();
		function updata(){
			var firstTop;
			var paddingTop = 0;
			var pos = jQuery(window).scrollTop();
			$this.each(function(){
				if(jQuery(this).data('upb_br_animation')=='upb_fade_animation'){
					firstTop = jQuery(this).offset().top;
					var $element = jQuery(this);
					var top = $element.offset().top;
					var height = getHeight($element);
					if (top + height < pos || top > pos + windowHeight-100) {
						return;
					}
					var pos_change = prev_pos-pos;
					if ((top+height)-windowHeight < pos) {
						var op_c = (pos_change/windowHeight);
						if(applyTo=='parent'){
							var op = parseInt(jQuery(this).css('opacity'));
							op += op_c/2.3;
							jQuery(this).parents('.wpb_row').css({opacity :op})
						}
						if(applyTo=='self'){
							var op = parseInt(jQuery(this).css('opacity'));
							op += op_c/2.3;
							jQuery(this).css({opacity :op})
						}
					}
					prev_pos = pos;
				}
			});
		}
		jQuery(window).bind('scroll', updata).resize(updata);
		updata();
	}
}( jQuery ));
 // Auto Initialization
 jQuery(document).ready(function(){
	 
	 var temp_vdo_pos = 0;
	 
 	//if(!jQuery.browser.mobile){
	 	jQuery('.upb_content_video, .upb_content_iframe').prev().css('background-image','').css('background-repeat','');
		jQuery('.upb_content_video').ultimate_video_bg();
		jQuery('.upb_bg_img').ultimate_bg_shift();
		jQuery('.upb_content_iframe').ultimate_video_bg();
		jQuery('.upb_grad').ultimate_grad_shift();
		jQuery('.upb_color').ultimate_bg_color_shift();

		//jQuery('.upb_no_bg').prev().css('background-image','').css('background-repeat','');
		jQuery('.upb_no_bg').each(function(index, nobg) {
            var no_bg_fadeout = jQuery(nobg).attr('data-fadeout');
			var fadeout_percentage = jQuery(nobg).data('fadeout-percentage');
			var parallax_content = jQuery(nobg).data('parallax-content');
			var parallax_content_sense = jQuery(nobg).data('parallax-content-sense');
			
			var disble_mobile = jQuery(nobg).data('row-effect-mobile-disable');
			jQuery(nobg).prev().attr('row-effect-mobile-disable',disble_mobile);
			
			if(no_bg_fadeout == 'fadeout_row_value')
			{
				jQuery(nobg).prev().addClass('vc-row-fade');
				jQuery(nobg).prev().data('fadeout-percentage',fadeout_percentage);
			}
			if(parallax_content == 'parallax_content_value')
			{
				jQuery(nobg).prev().addClass('vc-row-translate');
				jQuery(nobg).prev().attr('data-parallax-content-sense', parallax_content_sense);
			}
        });
		jQuery('.upb_no_bg').remove();
		
		
		//jQuery('.upb_row_bg').ultimate_parallax_animation('parent');
		var resizees = function(){
	    	jQuery('.upb_row_bg').each(function() {
				var ride = jQuery(this).data('bg-override');
				var ancenstor,parent;
				parent = jQuery(this).parent();
				if(ride=='browser_size'){
					ancenstor=jQuery('html');
				}
				if(ride == 'ex-full'){
					ancenstor = jQuery('html');
				}
				else if(ride == 'full'){
					ancenstor = jQuery('body');
				}
				//if ( isNaN( ride ) ) {	return;	}
				else if(! isNaN(ride)){
					ancenstor = parent;
					for ( var i = 0; i < ride; i++ ) {
						if ( ancenstor.is('html') ) {
							break;
						}
						ancenstor = ancenstor.parent();
					}
				}
				var al= parseInt( ancenstor.css('paddingLeft') );
				var ar= parseInt( ancenstor.css('paddingRight') )
				//console.log(al+' '+ar);
				var w = al+ar + ancenstor.width();
				var bl = - ( parent.offset().left - ancenstor.offset().left );
				//console.log(bl);
				if ( bl > 0 ) {	left = 0; }
				jQuery(this).css({'width': w,'left': bl	})
				if(ride=='browser_size'){
					parent.css('min-height',jQuery(window).height()+'px');
					var rheight = jQuery(this);
					//console.log(jQuery(this).find('.upb-background-text-wrapper'));
					if(parent.find('.upb-background-text-wrapper').length > 0)
					{
						parent.find('.upb-background-text-wrapper').css('min-height',jQuery(window).height()+'px');
					}
				}
			});
			
			jQuery(window).load(function(){
				jQuery('.upb_video-bg').each(function(index,ele) {
					var ride = jQuery(this).data('bg-override');
					var ancenstor,parent;
					parent = jQuery(this).parents('.wpb_row');
					if(ride=='browser_size'){
						ancenstor=jQuery('html');
					}
					if(ride == 'ex-full'){
						ancenstor = jQuery('html');
						jQuery(this).parents('.upb_video_class').css('overflow','visible');
					}
					else if(ride == 'full'){
						ancenstor = jQuery('body');
						jQuery(this).parents('.upb_video_class').css('overflow','visible');
					}
					//if ( isNaN( ride ) ) {	return;	}
					else if(! isNaN(ride)){
						ancenstor = parent;
						for ( var i = 1; i < ride; i++ ) {
							if ( ancenstor.is('html') ) {
								break;
							}
							ancenstor = ancenstor.parent();
						}
					}
					var al= parseInt( ancenstor.css('paddingLeft') );
					var ar= parseInt( ancenstor.css('paddingRight') );
					var vc_margin = parseInt( ancenstor.css('marginLeft') ); //vc row margin
					var w = ancenstor.outerWidth();
					var vdo_left = jQuery(this).offset().left;
					var vdo_left_pos = jQuery(this).position().left;
					var div_left = ancenstor.offset().left;
					var cal_left = div_left - vdo_left;
					if(vdo_left_pos < 0)
						cal_left = vdo_left_pos + cal_left;
						
					if(index == 0)
						temp_vdo_pos = vdo_left_pos;
					if(temp_vdo_pos > 0)
						cal_left = temp_vdo_pos;
								
					jQuery(this).css({'width': w,'min-width':w,'left': cal_left });
					var ratio =(16/9);
					pHeight = Math.ceil(w / ratio);
					children = jQuery(this).children();
			
					children.css({'width': w,'min-width':w});
					
					var is_poster = jQuery(this).css('background-image');
					
					if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )
					{
						children.css({'height':pHeight});
						if(ride=='browser_size'){
							parent.addClass('video-browser-size');
							parent.css('min-height',jQuery(window).height()+'px');
						
							if(parent.find('.upb_video-text-wrapper').length > 0)
							{
								parent.find('.upb_video-text-wrapper').addClass('full-browser-size');
								parent.find('.upb_video-text-wrapper').css('min-height',jQuery(window).height()+'px');
							}
						}
					}
					else
					{
						//if(ride=='browser_size')
						//{
							if(typeof is_poster === 'undefined' || is_poster == 'none')
							{
								children.css({'max-height':'auto','height':'auto'});
								parent.css('min-height','auto');
							}
						//}
					}
				});
			});
			/*jQuery('.upb_bg_size_automatic.vcpb-vz-jquery').each(function(){
				var vh = jQuery(window).outerHeight();
				var bh = jQuery(this).parent().outerHeight();
				var speed = jQuery(this).data('parallax_sense');
				var ih = (((vh+bh)/100)*speed)+bh;
				jQuery(this).css('background-size','auto '+ih+'px')
			})
			jQuery('.upb_bg_size_automatic.vcpb-hz-jquery').each(function(){
				var vh = jQuery(window).outerHeight();
				var bh = jQuery(this).parent().outerHeight();
				var speed = jQuery(this).data('parallax_sense');
				var bw = jQuery(this).outerWidth()
				var ih = (((vh+bh)/100)*speed)+bw;
				jQuery(this).css('background-size',ih+'px auto');
			})
			jQuery('.upb_bg_size_automatic.vcpb-hz-jquery').each(function(){
			})*/
		};
		resizees();
		//jQuery('.upb_video-bg').parents('.upb_video_class').css('overflow','visible');
		jQuery(window).load(function(){
			resizees();
		});
		jQuery(window).resize(function(){
			resizees();
		});
		//jQuery('.upb_video_class').ultimate_parallax_animation('self');
	//}
		jQuery('.video-controls').click(function(e) {
            var current_action = jQuery(this).attr('data-action');
			//var type = jQuery(this).attr('data-type');
			
			//if(type != 'youtube')
			//{
				var vdo = jQuery(this).parent().find('.upb_video-src');
				if(current_action == 'pause')
				{
					jQuery(this).attr('data-action','play');
					vdo[0].play();
					jQuery(this).html('<i class="wooicon-pause"></i>');
				}
				else
				{
					jQuery(this).attr('data-action','pause');
					vdo[0].pause();
					jQuery(this).html('<i class="wooicon-play"></i>');
				}
				
				if(vdo.hasClass('enable-on-viewport'))
				{
					vdo.addClass('override-controls');
				}
			//}
        });
		
		//row animation execution
		jQuery('.vcpb-animated').each(function(index, element) {
			var repeat = jQuery(element).data('animation-repeat');
			jQuery(this).css({'background-repeat':repeat});
			var mobile_disable = jQuery(element).parent().attr('data-row-effect-mobile-disable');
			if(typeof mobile_disable == "undefined")
				mobile_disable = 'false';
			else
				mobile_disable = mobile_disable.toString();
			if(! /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )
				var is_mobile = 'false';
			else
				var is_mobile = 'true';
			if(is_mobile == 'true' && mobile_disable == 'true')
				var disable_row_effect = 'true';
			else
				var disable_row_effect = 'false';
			if(disable_row_effect == 'false')
			{
				var scrollSpeed = 10;
				if(jQuery(this).attr('data-parallax_sense') != '')
					scrollSpeed = jQuery(this).attr('data-parallax_sense');
					
				scrollSpeed = 100 - scrollSpeed;	
				
				var animation_type = jQuery(this).attr('data-bg-animation-type');
				var animation = jQuery(this).attr('data-bg-animation');
				
				// set the default position
				var current = 0;
				// set the direction
				var direction = animation_type;
				//Calls the scrolling function repeatedly
				setInterval(function(e){
					if(animation == 'right-animation' || animation == 'bottom-animation')
						current -= 1;
					else
						current += 1;
					jQuery(element).css("backgroundPosition", (direction == 'h') ? current+"px 0" : "0 " + current+"px");
				}, scrollSpeed);
			}
        });
 });
	