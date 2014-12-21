var resizedd=0;
var time_f_arr =[];
jQuery(document).ready(function(){	
	make_info_circle('.info-c-full-br',0);
	responsive_check('.info-c-full-br');
	//make_info_circle('.info-c-semi-br',0);
	//responsive_check('.info-c-semi-br');
	//part_circle_icon('.info-c-full-br');
	//semi_circle_icon('.info-c-semi-br');			
	jQuery(window).resize(function(){
		resizedd++;
		make_info_circle('.info-c-full-br',resizedd);
		//make_info_circle('.info-c-semi-br',resizedd);
	})	
	jQuery('.info-c-full-br').each(function(){
		if(jQuery(this).data('focus-on')=="click"){
			jQuery(this).find('.icon-circle-list .info-circle-icons').click(function(){		
				var obj = jQuery(this);		
				jQuery(this).parents('.info-c-full-br').attr('data-slide-true','false');
				show_next_info_circle(obj);
			})
		}
		if(jQuery(this).data('focus-on')=="hover"){
			jQuery(this).find('.icon-circle-list .info-circle-icons').hover(function(){		
				var obj = jQuery(this);		
				jQuery(this).parents('.info-c-full-br').attr('data-slide-true','false');
				show_next_info_circle(obj);
			},function(){})	
		}
	})
	/*jQuery('.info-c-semi-br .icon-circle-list .info-circle-icons').bind('click',function(){
		var txt = jQuery(this).find('.text').html();
		var highlight_style= jQuery(this).parents('.info-c-full-br').data('highlight-style');
		if(highlight_style==''){
			//console.warn("Info Circle Higlight style not defined")
		}else{
			jQuery('.'+highlight_style).removeClass(highlight_style).removeClass('info-cirlce-active');
			jQuery(this).addClass(highlight_style).addClass('info-cirlce-active');
		}
		jQuery(this).parents('.info-c-semi-br').find('.info-c-semi').html(txt);
	})
	*/
	//jQuery('.info-c-full-br').attr('data-slide-true','true');	
	setTimeout(function() {		
		jQuery('.info-c-full-br').each(function(){
			var slide_delay = jQuery(this).data('slide-duration');
			if(!slide_delay){
				slide_delay = 0.2;
			}
			jQuery(this).attr('data-slide-number','1');
			info_circle_slide((slide_delay*1000),jQuery(this));
			//if(jQuery(this).attr('data-slide-true')=='off'){
				var obj = jQuery(this).find('.info-circle-icons').eq(0);			
				show_next_info_circle(obj);
			//}
		});		
	}, 1000); 
})
function info_circle_slide (delay,identity) {
	identity.bsf_appear(function(){
		setInterval(function(){
			if(identity.attr('data-slide-true')=='on'){				
				var myindex = identity.attr('data-slide-number')*1;
				//console.log('slideTo'+myindex);
				var len = identity.find('.info-circle-icons').length;		
				if(identity.data('info-circle-angle')!='full'){
					if(len-1 == myindex)
						myindex=0;
				}else{
					if(len== myindex)
						myindex=0;
				}
				var obj = identity.find('.info-circle-icons').eq(myindex);
				identity.attr('data-slide-number',myindex+1);
				show_next_info_circle(obj);
			}
		},delay)
		/*
		clearTimeout(time_f_arr[identity.data('timeout-fn')]);
		var time_fn = 'tf'+(Math.random().toString(36).slice(2));
		identity.data('timeout-fn',time_fn);
		time_f_arr[time_fn] = setTimeout(function() {
			if(identity.attr('data-slide-true')=='true'){		
				info_circle_slide(++myindex,delay,identity);
			}
		}, delay);*/
	});
}
function show_next_info_circle(obj){
	var highlight_style= obj.parents('.info-c-full-br').data('highlight-style');
	if(highlight_style==''){
		//console.warn('Info Circle Higlight style not defined')
	}else{
		obj.parents('.info-c-full-br').find('.'+highlight_style).removeClass(highlight_style).removeClass('info-cirlce-active');
		obj.addClass(highlight_style).addClass('info-cirlce-active');
	}
	var txt = obj.next();
	var cont_f_size =obj.parents('.info-c-full-br').data('icon-show-size');
	if(obj.parents('.info-c-full-br').data('icon-show')=='not-show'){
		txt.find('i').remove();
		txt.find('img').remove();
		obj.parents('.info-c-full-br').find('.info-c-full').addClass('cirlce-noicon');
	}
	txt = txt.html();
	//obj.parents('.icon-circle-list').find('.info-details').animate({opacity:0});
	//obj.next().animate({opacity:1});						
	//obj.parents('.info-c-full-br').find('.info-details').animate({opacity:0},'slow');
	var size = obj.css('font-size');
	var bg_col = obj.attr('style') 
	var p = bg_col.indexOf('background-color');		
	bg_col = bg_col.substr(p);
	bg_col = bg_col.split(';');
	bg_col= bg_col[0];
	bg_col = bg_col.substr(17);
	var obj_par = obj.parents('.info-c-full-br');
	obj_par.find('.info-c-full-wrap').stop().animate({opacity:0},'slow',function(){
	//obj.parents('.info-c-full-br').find('.info-c-full').animate({opacity:1},'fast',function(){
		//obj.parents('.info-c-full-br').find('.info-c-full').css('color',obj.css('color'));
		obj.parents('.info-c-full-br').find('.info-c-full .info-c-full-wrap').html(txt);
		//obj.parents('.info-c-full-br').find('.info-c-full i').css({'font-size':(parseInt(size)*2.5)+'px'});
		//obj.parents('.info-c-full-br').find('.info-c-full img').css({'width':(parseInt(size)*2.5)+'px','margin-top':'20px'});
		obj_par.find('.info-c-full i').css({'font-size':parseInt(cont_f_size)+'px'});
		obj_par.find('.info-c-full img').css({'width':parseInt(cont_f_size)+'px'});
		//obj.parents('.info-c-full-br').find('.info-c-full').css('background-color',bg_col);	
		obj.parents('.info-c-full-br').find('.info-c-full-wrap').animate({opacity:1},'slow');
	});
}
function responsive_check(obj){
	jQuery(obj).each(function(){
		if(jQuery(this).data('responsive-circle')=='on'){
			var circle_list = jQuery(this).parent().find('.smile_icon_list_wrap .smile_icon_list');
			var circle_list_item = circle_list.find('.icon_list_item').clone();
			circle_list.find('.icon_list_item').remove();
			var list_bg_col = jQuery(this).next().data('content_bg')
			var list_col = jQuery(this).next().data('content_color')			
			jQuery(this).find('.icon-circle-list .info-details').each(function(){
				var heading = jQuery(this).find('.info-circle-heading').html();
				var text = jQuery(this).find('.info-circle-text').html();
				var bg = jQuery(this).prev().css('background-color');
				var color = jQuery(this).prev().css('color');
				var border_style = jQuery(this).prev().css('border');
				var icon = jQuery(this).find('.info-circle-sub-def').children().eq(0).clone();												
				circle_list_item.find('.icon_list_icon').html(icon.wrap("<div />").parent().html());
				circle_list_item.find('.icon_description').css('color',list_col);
				circle_list_item.find('.icon_description').css('background-color',list_bg_col);
				circle_list_item.find('.icon_description h3').html(heading);
				circle_list_item.find('.icon_description p').html(text);
				circle_list_item.find('.icon_list_icon').css({'background-color':bg,'color':color,'border':border_style});
				circle_list.append(circle_list_item.wrap("<div />").parent().html());
			});
		}
	})
}
function make_info_circle(selector,resized){
	jQuery(selector).each(function(){		
		var f_size = jQuery(this).data('icon-size');			
		jQuery(this).parent().css({'margin-top':(f_size)+'px','margin-bottom':(f_size)+'px'})
		jQuery(this).find(".icon-circle-list .info-circle-icons").css({"font-size":f_size+'px','height':(f_size*2)+'px','width':(f_size*2)+'px','margin':'-'+(f_size+'px'),'line-height':(f_size*2)+'px'})
	})
	if(selector=='.info-c-full-br'){
		jQuery(selector).each(function(){			
			jQuery(this).css('height',jQuery(this).width());
			jQuery(this).css('opacity','1');
		})
	}
	if(selector=='.info-c-semi-br'){
		jQuery(selector).each(function(){
			var widd = jQuery(this).width();
			jQuery(this).css('height',((parseInt(widd))/2)+'px');
			var widd = widd+'px '+widd+'px '+' 0 0';			
			jQuery(this).css('border-radius',widd);
			var i_widd = jQuery(this).find('.info-c-full').width();
			i_widd = i_widd+'px '+i_widd+'px '+'0 0';			
			jQuery(this).find('.info-c-full').css('border-radius',i_widd);
		})
	}	
	setTimeout(function() {
		if(resized == resizedd){
			if(selector=='.info-c-full-br'){
				part_circle_icon(selector);	
			}
			if(selector=='.info-c-semi-br'){
				semi_circle_icon(selector);	
			}
		}
	}, 1000);	
}
function part_circle_icon(selector) {	
	jQuery(selector).each(function(){
		jQuery(this).bsf_appear(function(){
			if(jQuery(this).css('display')!='none'){
					var count = jQuery(this).find('.icon-circle-list .info-circle-icons').length;		
					var p_arr=new Array();
					var r=(jQuery(this).width())/2;
					var alt= 180/count;
					var pos=jQuery(this).data('info-circle-angle');				
					var dev = 	jQuery(this).data('divert');
					if(pos == 'full'){
						pos = 180;
						alt=90;
					}
					//pos = 180; // del on all option
					for(i=1;i<=count;i++)
					{
						var angle = i * ((180+(2*alt))/count);
					    angle = angle+pos-alt+dev;
					  	//Do not delete these Comments
					  	//var angle = i * (90)/count;
					    //angle = angle+270;
					    //var  angle = i* 360/count;
					    //angle = angle+90;
					    angle = (angle*0.0174532925);
					    p_arr.push( r * Math.cos(angle));
					    p_arr.push( r * Math.sin(angle));
					}
					var i=0, delay=0;
					var launch = jQuery(this).data('launch');
					var launch_duration = jQuery(this).data('launch-duration');
					var launch_delay = jQuery(this).data('launch-delay');
					/*if(!launch){
						launch='easeOutExpo';
					}*/
					if(!launch_duration){
						launch_duration = 1;
					}
					if(!launch_delay){
						launch_delay= .15;
					}
					if(launch!=''){
						delay = -(launch_delay*1000);
						jQuery(this).find('.icon-circle-list .info-circle-icons').each(function(){
							var el = jQuery(this);
							delay+=(launch_delay*1000);
							setTimeout(function() {
								el.animate({opacity:1,left:p_arr[i++],top:p_arr[i++]},(launch_duration)*1000,launch);
							}, delay);
						})
					}
					else{
						jQuery(this).find('.icon-circle-list .info-circle-icons').each(function(){
							var el = jQuery(this);						
							//el.animate({opacity:1,left:p_arr[i++],top:p_arr[i++]},(launch_duration)*1000,launch);
							el.css({'opacity':'1','left':p_arr[i++],"top":p_arr[i++]});
						})	
					}
			}
		});
	});
}
/*function semi_circle_icon(selector) {
	jQuery(selector).each(function(){
		if(jQuery(this).css('display')!='none'){
		var count = jQuery(this).find('.icon-circle-list .info-circle-icons').length;		
		var p_arr=new Array();
		var r=(jQuery(this).width())/2;		
		var alt= 180/count;
		var pos = 180;
		var dev = 	jQuery(this).data('divert');
		for(i=1;i<=count;i++)
		{
			var angle = i * ((180+(2*alt))/count);
		    angle = angle+pos-alt+dev;
		  	//Do not delete these Comments
		  	//var angle = i * (90)/count;
		    //angle = angle+270;
		    //var  angle = i* 360/count;
		    //angle = angle+90;
		    angle = (angle*0.0174532925);
		    p_arr.push( r * Math.cos(angle));
		    p_arr.push( r * Math.sin(angle));
		}
		var i=0, delay=0;
		var launch = jQuery(this).data('launch');
		var launch_duration = jQuery(this).data('launch-duration');
		var launch_delay = jQuery(this).data('launch-delay');
		if(!launch){
			launch='easeOutExpo';
		}
		if(!launch_duration){
			launch_duration = 1;
		}
		if(!launch_delay){
			launch_delay= .15;
		}
		delay = -(launch_delay*1000);
		jQuery(this).find('.icon-circle-list .info-circle-icons').each(function(){
			var el = jQuery(this);
			delay+=(launch_delay*1000);
			setTimeout(function() {
				el.animate({opacity:1,left:p_arr[i++],top:p_arr[i++]},(launch_duration)*1000,launch);
			}, delay);
		})
	}		
	})	
}*/
jQuery(window).load(function(){
	jQuery('.info-c-full-br').each(function(){
		if(jQuery(this).attr('data-slide-true')=='on'){
			jQuery(this).hover(function(){
				jQuery(this).attr('data-slide-true','off');
			},function(){
				jQuery(this).attr('data-slide-true','on');
				//info_circle_slide((jQuery(this).data('slide-duration'))*(1000),jQuery(this));
			})
		}
	})
});

jQuery(document).ready(function(e) {
    jQuery(".icon_list_item").each(function(index, element) {
        var $this = jQuery(this);
		var count = $this.find(".info-circle-img-icon").length;
		if(count >= 1 ){
			$this.closest("ul.smile_icon_list").addClass("ic-resp-img");
		}
    });
});