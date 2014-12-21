jQuery(document).ready(function($) {
								
								
	$('.mymail-amazonses-api').on('change', function(){
		
		($(this).is(':checked'))
			? $('.amazonses-tab-smtp').slideDown()
			: $('.amazonses-tab-smtp').slideUp();
		
	});
	
});
