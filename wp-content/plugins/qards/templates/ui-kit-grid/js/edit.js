(function(component, edit) {
	if (component.find('.grid').exists()) {
		component.find('.grid').each(function(index, element) {
			
			$(element).on('blur',function(){
				getEvenHeight($(this));
			});
			
			$(window).on('resize load',function(){
				getEvenHeight(element);
				
				$(element).find('.image').each(function(i, image){
					$(image).height(($(image).width()/16) * 9);
				});
			});

		});
	}
});

function getEvenHeight(grid){
	var maxHeight = 0;
	$(grid).find('li').each(function(index, element) {
		var height = $(element).removeAttr('style').height();
		if (height > maxHeight) maxHeight = height;
	});
	
	if ($(window).width() > 568){
		$(grid).find('li').each(function(index, element) {
			$(element).height(maxHeight + "px");
		});
	}
}