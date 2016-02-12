<?php
/**
 * New Post Administration Screen.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** Load WordPress Administration Bootstrap */
require_once('../../../wp-admin/admin.php');

/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global WP_Post $post
 */
global $post_type, $post_type_object, $post;
function addnewcase(){
	// Создаём объект записи
	  $my_post = array(
		 'post_title' => $_POST['title'],
		 'post_content' => $_POST['content'],
		 'post_status' => 'publish',
		 'post_author' => 1,
		 'post_type' => 'cases',
		 'post_category' => array(8,39)
	  );

	// Вставляем запись в базу данных
	$post_id = wp_insert_post( $my_post );
	$post = get_post($post_id);
	//Добавляем в созданный пост мету и таксономии
	/** Save date end
	 * field name: cp_to
	 */
	if (isset($_POST['cp_to'])) {
		$key = 'cp_to';
		$value = $_POST['cp_to'];
		update_post_meta( $post_id, $key, $value);
	}

	 /** Save date end
	 * field name: cp_from
	 */
	if (isset($_POST['cp_from'])) {
		$key = 'cp_from';
		$value = $_POST['cp_from'];
		update_post_meta( $post_id, $key, $value);
	}

	/*
	 * Save case category
	 * field name: cp_case_category
	 */
	if (isset($_POST['cp_case_category']) && $_POST['cp_case_category'] != ''){
		$terms = $_POST['cp_case_category'];
		$taxonomy = "functions";
		$append = false;
		wp_set_post_terms( $post_id, $terms, $taxonomy, $append );
	}

	if (isset($_POST['cp_case_branche']) && $_POST['cp_case_branche'] != ''){
		$terms = $_POST['cp_case_branche'];
		$taxonomy = "t-branche";
		$append = false;
		wp_set_post_terms( $post_id, $terms, $taxonomy, $append );
	}

	 /** Save date end
	 * field name: cp_date_end
	 */
	if (isset($_POST['date_end'])) {
		$key = 'cp_date_end';
		$value = $_POST['date_end'];
		update_post_meta( $post_id, $key, $value);
	}

	/*
	 * save result
	 */
	if (isset($_POST['cp_case_result'])) {
		$term = $_POST['cp_case_result'];
		$taxonomy = "results";
		$append = false;
		wp_set_post_terms( $post_id, $term, $taxonomy, $append );
	}

	/*
	 * save deadline
	 */
	if (isset($_POST['cp_date_deadline'])) {
		$key = 'deadline_cp';
		$timestamp = $_POST['cp_date_deadline'];
				
		update_post_meta( $post_id, $key, $timestamp);
	}
			
	/*
	 * Field "Members"
	 */
	if (isset($_POST['case_members'])) {
		
		$key = 'members-cp-posts-sql';
		$case_members = trim( $_POST['case_members'] );
		
		$case_members = explode(',', $case_members);

		$current_members = get_post_meta($post->ID, 'members-cp-posts-sql');
		

		//получаем массив участников, которых убрали из поля
		$members_remove = array_diff($current_members, $case_members);
		
		//получаем массив участников, которых добавили
		$members_add = array_diff($case_members, $current_members);

		//Проверяем есть ли ответственный и если есть, то включаем его в список участников на проверку
		$responsible_id = get_post_meta($post->ID, 'responsible-cp-posts-sql', true);

		//удаляем лишних учатсников
		foreach($members_remove as $member){
			
			//если участника на удаление есть в поле Ответственный, то пропускаем удаление
			if($responsible_id == $member) continue;
			
			//удаляем участника из списка
			delete_post_meta($post->ID, 'members-cp-posts-sql', trim($member));
		}

		//удаляем лишних учатсников
		foreach($members_add as $member){
			add_post_meta($post->ID, 'members-cp-posts-sql', trim($member));
		}
	}   



	/*
	 * Field "Responsible"
	 */
	if (isset($_POST['cp_responsible'])) {

		$data = trim( $_POST['cp_responsible'] );
		$key = 'responsible-cp-posts-sql';
		
		if(empty($data)) delete_post_meta($post_id, $key);

		update_post_meta($post_id, $key, $data);
	}

	/*
	 * Field "Post Parent"
	 */
	// infinity loop  fixed
	if (isset($_POST['cp_case_post_parent'])) {

		$post_parent = trim( $_POST['cp_case_post_parent'] );
		if ($post_parent > 0 && $post->post_parent != $post_parent){
		//unhook
			remove_action( 'save_post', array($this, 'save_data_post'));
			wp_update_post(array(
				'ID' => $post_id, 
				'post_parent' => $post_parent
			)); 
		//rehook
			add_action( 'save_post', array($this, 'save_data_post'));
		}
	} 

	?>
	<script type="text/javascript">
	setTimeout('location.replace(<?php echo'"' . get_permalink($post) . '"'; ?>)', 0,1);
	</script>
	<?php
}
addnewcase();



?>