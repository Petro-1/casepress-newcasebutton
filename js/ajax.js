jQuery(document).ready(function(){
	var ncb_caseadded = 0, //переменная, обозначающая был ли уже добавлен пост
		ncb_newpostid = 0; //переменная, в которую сохраняется ID созданного поста
	jQuery(document).on( 'click', '#ncb_submit', function( event ) {
		event.preventDefault();
		/* без вп эдитора не нужно
		//Имитирую нажатие кнопок перехода в html эдитор и обратно, если открыт tinymce editor, т.к. только в таком случае содержимое передается корректно 
		if (jQuery('#wp-txtrID-wrap').attr("class") != 'wp-core-ui wp-editor-wrap html-active') {
			jQuery('#txtrID-html').trigger('click');
			jQuery('#txtrID-tmce').trigger('click');
		}
		*/
		jQuery.ajax({
			url: ncbjsvar.ajaxurl,
			type: 'post',
			//Отправка всей формы через аякс выводит ошибки из файла cases_view.php
			data: jQuery("#ncb_form").serialize() +
			'&ncb_caseadded=' + ncb_caseadded + //передаю обработчику был ли уже создан ли пост
			'&ncb_newpostid=' + ncb_newpostid + //и ID созданного поста
			'&action=ncb_script',
			success: function(response, textStatus, XMLHttpRequest){
				jQuery('#ncb_message').css("display" , "block");
				jQuery('#ncb_message').html(response);
				jQuery('#ncb_submit').val('Обновить');
				ncb_caseadded = 1; //дело добавлено
				ncb_newpostid = jQuery('#ncb_newpostid').val(); //берем ID созданного поста из скрытого поля
			}
		});

	});
});
