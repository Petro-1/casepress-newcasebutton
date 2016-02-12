jQuery(document).ready(function($) {

  var data = {
    action: 'my_action',
    security : MyAjax.security,
    whatever: 1234
  };
  
  $("#popupchild").load(MyAjax.formurl);
 
});