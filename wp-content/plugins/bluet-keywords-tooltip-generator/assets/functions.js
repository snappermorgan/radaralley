jQuery(document).ready(function(){

	if(jQuery('.bluet_tooltip').length==0)
	return;

	bleutExcludeKwStyle();	
	//add listener to checkboxes
	jQuery("#bluet_kw_admin_div_terms li input").each(function(ind){
		jQuery(this).change(function(){
			bleutExcludeKwStyle();
		});
	});
	hideIfChecked('bluet_kw_admin_exclude_post_from_matching_id','bluet_kw_admin_div_terms');

	//array contains tabs to show
	var bluet_tab=['bluet_style_tab','bluet_settings_tab','bluet_excluded_tab'];

	for(var i=0;i<bluet_tab.length;i++){

		//remove active class from all elements
		jQuery('#'+bluet_tab[i]).removeClass('nav-tab-active');
		
		var tabular=document.getElementById(bluet_tab[i]).addEventListener('click',function(e){
			for(var i=0;i<bluet_tab.length;i++){
				jQuery('#'+bluet_tab[i]).removeClass('nav-tab-active');

			}
			
			//tab we want to show
			var tabToShow=e.target.dataset.tab
			
			bluetShowTab(tabToShow);
			
			jQuery(e.target).addClass('nav-tab-active');
		},false);
	}

	//begin by displaying the style div
	bluetShowTab("bluet-section-style");
	jQuery('#bluet_style_tab').addClass('nav-tab-active');
	
	//
	bluet_hide_bg();		
	document.getElementById("bluet_kw_no_background").addEventListener("change",bluet_hide_bg,false);
	
	for(var i=0;i<document.getElementsByClassName('wp-picker-holder').length;i++){
		document.getElementsByClassName('wp-picker-holder')[i].addEventListener('mousemove',function(e){
			
			if(!document.getElementById("bluet_kw_no_background").checked){
				var bluet_keyword_bg=document.getElementsByName('bluet_kw_style[bt_kw_tt_bg_color]')[0].value;
			}else{
				var bluet_keyword_bg='initial';
			}
			
			var bluet_keyword_color=document.getElementsByName('bluet_kw_style[bt_kw_tt_color]')[0].value;
			var bluet_tooltip_bg=document.getElementsByName('bluet_kw_style[bt_kw_desc_bg_color]')[0].value;
			var bluet_tooltip_color=document.getElementsByName('bluet_kw_style[bt_kw_desc_color]')[0].value;
			
			document.getElementsByClassName('bluet_tooltip')[0].style.backgroundColor=bluet_keyword_bg;
			document.getElementsByClassName('bluet_tooltip')[0].style.color=bluet_keyword_color;
			document.getElementsByClassName('bluet_block_container')[0].style.backgroundColor=bluet_tooltip_bg;
			document.getElementsByClassName('bluet_block_container')[0].style.color=bluet_tooltip_color;
			
		},false);
	}
});
/**/

//
function bluetShowTab(tabId){
	var mybluet_my_div_settings=document.getElementById('bluet-sections-div');
	var mybluet_children=mybluet_my_div_settings.childNodes;

	for(var i=0;i<mybluet_children.length-1;i++){
		mybluet_children[i].style.display='none';
	}
	
	document.getElementById(tabId).style.display='block';
}

function bluet_hide_bg(){
		elem=document.getElementsByClassName('bluet_tooltip')[0];
		txt_color=elem.style.color;
	if(document.getElementById("bluet_kw_no_background").checked){
		elem.style.backgroundColor='initial';		
		elem.style.borderBottom =txt_color+" 1px dotted";
		elem.style.borderRadius="0px";
		elem.style.padding="0px";		

		document.getElementById('bluet_kw_bg_hide').style.display='none';
	}else{
		elem.style.backgroundColor=document.getElementsByName('bluet_kw_style[bt_kw_tt_bg_color]')[0].value;
		document.getElementById('bluet_kw_bg_hide').style.display='block';
		elem.style.borderBottom="0px";

		elem.style.borderRadius="10px";
		elem.style.padding="1px 7px 4px 7px";		
	}
}

//for the edit page 

function hideIfChecked(myId,idToDeal){
	if(jQuery("#"+myId).attr('checked')){
		jQuery("#"+idToDeal).hide();
	}else{
		jQuery("#"+idToDeal).show();
	}
}

function bleutExcludeKwStyle(){
	//if no checkbox is checked exit
	checked_ones=0;
	jQuery("#bluet_kw_admin_div_terms li input").each(function(ind){
		if(jQuery(this).attr("checked"))
			checked_ones++;
	});
	
	if(checked_ones < 1){
		jQuery("#bluet_kw_admin_div_terms li").css("text-decoration","initial");
		return;
	}

   jQuery("#bluet_kw_admin_div_terms li").css("text-decoration","line-through")
   
   jQuery("#bluet_kw_admin_div_terms li").each(function(ind){
		if(jQuery("#bluet_kw_admin_div_terms li input").eq(ind).attr("checked")){
			jQuery("#bluet_kw_admin_div_terms li").eq(ind).css("text-decoration","initial");
		}
   })
}
/**/