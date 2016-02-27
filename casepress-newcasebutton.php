<?php
/**
 * @package Casepress-newcasebutton
 * @version 1.0
 */
/*
Plugin Name: Casepress new case fixed button
Plugin URI: -
Description: A fixed button for a new case for casepress
Author: Petro-1
Version: 1.0
Author URI: -
*/

//Добавляем кнопку и модальное окно
function showbutton() {
	?>	

	<!-- Button trigger modal -->
	<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" id="ncb_button"
			style="	position: fixed; bottom: 0px; right: 0px; margin-right:5%; margin-bottom:5%; border-radius:50%;
					box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);"
			onmouseover="this.style.boxShadow = '0 2px 8px 0 rgba(0, 0, 0, 0.2), 0 3px 20px 0 rgba(0, 0, 0, 0.19)';"
			onmouseout="this.style.boxShadow = '0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19)';">
		<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
	</button>

	<!-- Modal -->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
					<h3 class="modal-title" id="myModalLabel">Добавить Дело</h3>
				</div>
				<!-- форма для создания дела -->
				<form action="" method="post" id="ncb_form">
					<div class="modal-body" id="ncb_content">
						<?php
						ncb_add_form();
						?>
					</div>
					<div id="ncb_message" style=" display: none;"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal" id="ncb_close">Закрыть
						</button>
						<input type="submit" class="btn btn-primary" value="Создать дело" id="ncb_submit"></input>
					</div>
				</form>

			</div>
		</div>
	</div>
	<?php
}
add_action('get_footer' , 'showbutton');




//Функция добавления формы для создания дела 
function ncb_add_form() {

	//создаем новое пустое дело, чтобы функция создания поля Участники проверяла тип поста и выводила поле.
	// Сохраняем ID этого поста в  опции, чтобы в дальнейшем в базу не добавлялись ID пустых постов.
	//В этой версии оставляем, так как нужен $post для форомы подразеделения, иначе значение по умолчанию будет из последней выведенной записи
	if (get_option('ncb_defpost')) {
		$post_id1 = get_option('ncb_defpost');
	} else {
		$post_id1 = wp_insert_post( 
			array (
			'post_type' => 'cases'
			)
		);
		add_option ('ncb_defpost', $post_id1);
	}
	$post = get_post($post_id1);

	?>
	<div class="row">
		<div class="col-md-8">
			<input required type="text" class="form-control input-lg" name="title" placeholder="Введите название дела..."></input><br />
			<?php

				/*
				//поле Участники --- остается загадкой, почему поле не отображается дальше заголовка на странице
				// с отдельным делом, все условия функции вроде точно также соблюдаются.
				CaseViewsAdminSingltone::getInstance()->form_case_members_render($post);
				*/


				//Описание дела
				//Textarea вместо wp_editor
				?>
				<div class="form-group">
					<label for="comment">Описание:</label>
					<textarea class="form-control" rows="15" name="content"></textarea>
				</div>
				<?php
				/*
				wp_editor( $post->content , 'txtrID', array(
					'textarea_name'=>'content',
					'tinymce'=>true ,
					'media_buttons' => true ,
					'teeny' => false,
					'drag_drop_upload' => true,
					'editor_height' => 210) );
				*/

			?>

		</div>
		<div class="col-md-4">
			<?php
				//Добавляем боковые поля.
				//*Только выбор подразделения
				CaseViewsAdminSingltone::getInstance()->add_field_case_branche($post);

				/*
				// При нажатии на поле выбора даты из-за того, что в rome.mini.css у .rd-container z-index = 1
				// всплывающее окошко не видно, так как оно находится на заднем плане относительно формы и модального окна.
				// Не нашел других решений, кроме как вручную менять z-index в этом файле.
				do_action('add_field_for_case_aside_parameters');
				*/
			?>
		</div>
	</div>
	<?php
}

/*
** AJAX
*/

//Подключаю jQuery, хотя он вроде и так уже подключен, но хуже ведь не будет
add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
function my_scripts_method(){
	wp_enqueue_script( 'jquery' );
}

//Добавляю скрипт для аякса и локализую переменные
add_action( 'wp_enqueue_scripts', 'ajax_test_enqueue_scripts' );
function ajax_test_enqueue_scripts() {
	wp_enqueue_script( 'ncb_script', plugins_url( 'js/ajax.js', __FILE__ ), array('jquery'), '1.0', true );
	wp_localize_script( 'ncb_script', 'ncbjsvar', 
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'pluginurl' => plugins_url( '/', __FILE__ ),
			'siteurl' => site_url('/')
		)
	);
}

//Обработка данных формы, отправленых через аякс, создание дела и вывод ссылки
add_action('wp_ajax_ncb_script', 'my_action_callback');
add_action('wp_ajax_nopriv_ncb_script', 'my_action_callback'); //незарегистрированным доступ не нужен
function my_action_callback() {
	//Создание нового поста, если еще не был создан
	if ($_REQUEST['ncb_caseadded'] == 0) {
		$my_post = array(
			//'ID' => $post->ID,
			'post_title' => $_POST['title'],
			'post_content' => $_POST['content'],
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'cases',
		);

		$post_id2 = wp_insert_post($my_post);
		$post = get_post($post_id2);

		//Делаю цикл, чтобы ID текущего поста равнялся ID созданного дела,
		// потому что иначе функция save_data_post возвращает ничего вследствие $post = get_post(), который без этого цикла становится ничем
		query_posts(array(
				'post_type' => 'cases',
				'order' => 'DESC')
		);
		if (have_posts()) {
			while ($post->ID != $post_id2) {

				the_post();
			}
		}
		wp_reset_postdata();

		//Добавление меты и таксономий через функцию save_data_post
		do_action('save_post');
		//CaseViewsAdminSingltone::getInstance()->save_data_post();
		echo '
		<div class="alert alert-success" role="alert" style="text-align:center;">
			Дело создано!
			<a href="' . get_permalink($post) . '" class="alert-link">
				 Нажмите для перехода.
			</a>
		</div>

		<!-- невидимое поле для передачи скрипту и возврата при следующем нажатии ID созданного поста -->
		<input type="submit" style="display: none" id="ncb_newpostid" value = "' . $post->ID . '"></input>
		';

	//если пост уже был создан, то обновляем существующий
	} else {

		$my_post = array(
			'ID' => $_REQUEST['ncb_newpostid'],
			'post_title' => $_POST['title'],
			'post_content' => $_POST['content'],
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'cases',
		);
		wp_update_post($my_post);

		//Цикл для обновления полей
		$post = get_post($_REQUEST['ncb_newpostid']);
		query_posts(array(
				'post_type' => 'cases',
				'order' => 'DESC')
		);
		if (have_posts()) {
			while ($post->ID != $_REQUEST['ncb_newpostid']) {

				the_post();
			}
		}
		wp_reset_postdata();

		do_action('save_post');
		echo '
		<div class="alert alert-success" role="alert" style="text-align:center;">
			Дело обновлено!
			<a href="' . get_permalink($post) . '" class="alert-link">
				 Нажмите для перехода.
			</a>
		</div>
		<!-- оставляем невидимое поле для следующего обновления -->
		<input type="submit" style="display: none" id="ncb_newpostid" value = "' . $_REQUEST['ncb_newpostid'] . '"></input>
		';

	}
	wp_die();
}

