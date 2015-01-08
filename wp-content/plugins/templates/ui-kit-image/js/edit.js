(function(component, edit) {
  //add loaded
  component.find('img').each(function(index, el) {
    (function(sel){
      if (sel.complete) {
        $(sel).parents('.image').addClass('loaded');
      } else {
        var loadFunc = function() {
          $(sel).parents('.image').addClass('loaded');
        };
        $(sel).load = loadFunc; 
        $(sel).on('load', loadFunc);
      }
    })(el);
  });
});