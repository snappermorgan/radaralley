(function(component, edit) {
	if (!edit) {
        var subscribeMessage = '';

        // subscribeData was initialized in subscribe1.html.twig and subscribe1.html.twig
        if(subscribeData) {
            for(var i in subscribeData) {
                if (subscribeData.hasOwnProperty(i)) {
                    subscribeMessage = subscribeData[i].join(' ');
                }
            }
            alert(subscribeMessage);
        }
	}
})