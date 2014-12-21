jQuery(document).ready(function(){
	bluet_placeTooltips(".bluet_tooltip, .bluet_img_tooltip");	 
	
	moveTooltipElementsTop(".bluet_block_to_show");
})

function moveTooltipElementsTop(className){
//this function moves les tooltip elements after the tag BODY
		jQuery("body").prepend("<div id='tooltip_blocks_to_show'></div>");
		
		jQuery("#tooltip_blocks_to_show").prepend(jQuery(className));
		
		//remove repeated elements 
		jQuery("#tooltip_blocks_to_show").children().each(function(){
			id_post_type=jQuery(this).data("tooltip");
			if(jQuery("#tooltip_blocks_to_show").children("[data-tooltip="+id_post_type+"]").length>1){
				jQuery("#tooltip_blocks_to_show").children("[data-tooltip="+id_post_type+"]").each(function(index){
					if(index>0){
						jQuery(this).remove();
					}
				});
			}
		});
		
	//add listeners to tooltips 
	jQuery(".bluet_block_to_show").mouseover(function(){
		jQuery(this).show();
	})
	jQuery(".bluet_block_to_show").mouseout(function(){
		jQuery(this).hide();
	})

}

function bluet_placeTooltips(inlineClass){
	//add listeners to inline keywords
	jQuery(inlineClass).mouseover(function(){
		//id of the posttype in concern
		
		id_post_type=jQuery(this).data("tooltip");
		var tooltipBlock=jQuery("#tooltip_blocks_to_show").children("[data-tooltip="+id_post_type+"]").first();
	  
		if(tooltipBlock){

			//Calculate the new Position
			var xTop=(jQuery(this).offset().top+jQuery(this).outerHeight(true));
			var yLeft=(jQuery(this).offset().left+(jQuery(this).outerWidth(false)/2)-tooltipBlock.outerWidth(true)/2);
			
			//to prevent to be before the left side of the doc
			if(yLeft<0){
				yLeft=0;
			}
			
			//to prevent to be before the right side of the doc
			if( jQuery(document).outerWidth() < (yLeft+tooltipBlock.outerWidth(true)) ){
				yLeft=yLeft-(yLeft+tooltipBlock.outerWidth(true)-jQuery(document).outerWidth())
			}
	
			tooltipBlock.show();
			tooltipBlock.offset({"top":xTop,"left":yLeft});

		}
	});//end mouseenter
	
	jQuery(inlineClass).mouseout(function(){
		id_post_type=jQuery(this).data("tooltip");
		var tooltipBlock=jQuery("#tooltip_blocks_to_show").children("[data-tooltip="+id_post_type+"]").first();

	   if(tooltipBlock){
	   tooltipBlock.hide();
		   
	   }
	});
}