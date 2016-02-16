jQuery(document).ready(function(){
	jQuery(document).on( 'click', '#ncb_submit', function( event ) {
		event.preventDefault();
		//Имитирую нажатие кнопок перехода в html эдитор и обратно, если открыт tinymce editor, т.к. только в таком случае содержимое передается корректно 
		if (jQuery('#wp-txtrID-wrap').attr("class") != 'wp-core-ui wp-editor-wrap html-active') {
			jQuery('#txtrID-html').trigger('click');
			jQuery('#txtrID-tmce').trigger('click');
		}
		jQuery.ajax({
			url: ncbjsvar.ajaxurl,
			type: 'post',
			//Отправка всей формы через аякс выводит ошибки из файла cases_view.php
			data: jQuery("#ncb_form").serialize() + '&action=ncb_script',
			success: function(response, textStatus, XMLHttpRequest){

			jQuery('#ncb_content').html(response);
			}
			
		});
	});
});
