<?php 
require_once('../../../wp-load.php');
require_once('../../../wp-admin/admin.php');
/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global WP_Post $post
 */
global $post_type, $post_type_object, $post;
	
//<form> left side
?>
<div id="maindiv">
<?php
	echo  '<form action="' . site_url() . '/wp-content/plugins/casepress-newcasebutton/action.php" method="post">'; 
?>
	<div class="col-md-9">
		
		<!-- TITLE -->
		<h3>Добавить Дело</h3>
		<input required type="text" class="form-control" name="title" placeholder="Enter title here"></input>
		
		<!-- MEMBERS -->
		<?php
			//if (($post->post_type != 'cases')) return;
			$members = get_post_meta($post->ID, 'members-cp-posts-sql');
		?>  
			<div id="case_members_wrapper">
				<div id="members_heading">
					<label>Участники</label>

				</div>
				<div id="case_members_edit_wrapper">
					<input type="hidden" id="case_members" name="case_members"/>
				</div>
				<script type="text/javascript">
					jQuery(document).ready(function($) {

						//Создаем поле Select2 + AJAX для выбора участников в деле
						$("#case_members").select2({
							placeholder: "Добавить участника...",
							formatInputTooShort: function (input, min) { return "Пожалуйста, введите " + (min - input.length) + " или более символов"; },
							minimumInputLength: 1,
							formatSearching: function () { return "Поиск..."; },
							formatNoMatches: function () { return "Ничего не найдено"; },
							width: '100%',
							multiple: true,
							ajax: {
								url: "<?php echo admin_url('admin-ajax.php') ?>",
								dataType: 'json',
								quietMillis: 100,
								data: function (term, page) { // page is the one-based page number tracked by Select2
									return {
										action: 'query_persons',
										page_limit: 10, // page size
										page: page, // page number
										//params: {contentType: "application/json;charset=utf-8"},
										q: term //search term
									};
								},
								results: function (data, page) {
									//alert(data.total);
									var more = (page * 10) < data.total; // whether or not there are more results available

									// notice we return the value of more so Select2 knows if more results can be loaded
									return {
										results: data.elements,
										more: more
										};
								}
							},

							formatResult: function(element){ return "<div>" + element.title + "</div>" }, // omitted for brevity, see the source of this page
							formatSelection: function(element){  return element.title; }, // omitted for brevity, see the source of this page
							dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
							escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
						});

						//Если есть данные о значении, то делаем выбор
						<?php
						if(! empty($members)):
							$members_data = array();
							foreach ($members as $member):
								if(!empty($member)) $members_data[] = array('id' => $member, 'title' => get_the_title($member));
							endforeach;
							?>
							$("#case_members").select2(
								"data", 
								<?php echo json_encode($members_data); ?>
							);
						<?php endif; ?>
					});
				</script>           
			</div>
		
		<!-- EDITOR -->
		
		<?php
		wp_editor( '', 'wpeditor', array('textarea_name' => 'content', 'textarea_rows' => 15) );
		?>
		

	
	</div>
<!-- right side -->
	<div class="col-md-3">
		
			<!-- Категория дела -->
			<div id="cp_case_category_div">
			   <?php

				$post_id = $post->ID;
				$taxonomy = 'functions';
				$terms = get_the_terms( $post_id, $taxonomy );

				//get first term from array
				if (is_array($terms)) $term = array_shift($terms);
				
				?>

				<label class="cp_label" for="cp_case_category_select">Категория дела</label>
				<?php
				$case_category_id = '0';
				
				if (isset($term->term_id)){
					$case_category_id = $term->term_id;
				} elseif (isset($_REQUEST['case_category_id'])) {
					$case_category_id = $_REQUEST['case_category_id'];
				} else $case_category_id = '0';

				wp_dropdown_categories( array(
					'name' => 'cp_case_category',
					'class' => 'cp_full_width',
					'id' => 'cp_case_category_select',
					'echo' => 1,
					'hide_empty' => 0, 
					'show_option_none' => 'Выберите категорию дела',
					'option_none_value' => '0',
					'selected' => $case_category_id,
					'hierarchical' => 1,
					'taxonomy' => 'functions'
				)) ; ?>

				<script type="text/javascript">
					jQuery(document).ready(function($) {
						 $('#cp_case_category_select').select2({
							width: '100%',
							allowClear: true,
						 });
					});
				</script>  

			</div>
		
			<!--------Подразделения-------->
			<div id="cp_case_branche_div">
			   <?php

				$post_id = $post->ID;
				$taxonomy = 't-branche';
				$terms = get_the_terms( $post_id, $taxonomy );

				//get first term from array
				if (is_array($terms)) $term = array_shift($terms);
				
				?>

				<label class="cp_label" for="cp_case_branche_select">Подразделение</label>
				<?php
				$case_branche_id = '0';
				
				if (isset($term->term_id)){
					$case_branche_id = $term->term_id;
				} elseif (isset($_REQUEST['case_branche_id'])) {
					$case_branche_id = $_REQUEST['case_branche_id'];
				} else $case_branche_id = '0';

				wp_dropdown_categories( array(
					'name' => 'cp_case_branche',
					'class' => 'cp_full_width',
					'id' => 'cp_case_branche_select',
					'echo' => 1,
					'hide_empty' => 0, 
					'show_option_none' => 'Выберите подразделение',
					'option_none_value' => '0',
					'selected' => $case_branche_id,
					'hierarchical' => 1,
					'taxonomy' => 't-branche'
				)) ; ?>

				<script type="text/javascript">
					jQuery(document).ready(function($) {
						 $('#cp_case_branche_select').select2({
							width: '100%',
							allowClear: true,
						 });
					});
				</script>  

			</div>
		
		<!-- ОТВЕТСТВЕННЫЙ -->
		<div id="cp_case_responsible_wrapper">
			<div>
				<div>
					<label class="cp_label" id="cp_case_responsible_label" for="cp_case_responsible_input" onclick="">Ответственный:</label>
				</div>
				<div id="cp_case_responsible_edit">
					<div id="cp_case_responsible_edit_input">
						<input type="hidden" id="cp_case_responsible_input" name="cp_responsible" class="cp_select2_single" />
					</div>  
				</div>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {

					$("#cp_case_responsible_input").select2({
						placeholder: "Выберите ответственного",
						width: '100%',
						allowClear: true,
						minimumInputLength: 1,
						ajax: {
								url: "<?php echo admin_url('admin-ajax.php') ?>",
								dataType: 'json',
								quietMillis: 100,
								data: function (term, page) { // page is the one-based page number tracked by Select2
										return {
												action: 'query_persons',
												page_limit: 10, // page size
												page: page, // page number
												//params: {contentType: "application/json;charset=utf-8"},
												q: term //search term
										};
								},
								results: function (data, page) {
										//alert(data.total);
										var more = (page * 10) < data.total; // whether or not there are more results available

										// notice we return the value of more so Select2 knows if more results can be loaded
										return {
												results: data.elements,
												more: more
												};
								}
						},
						
						formatResult: function(element){ return "<div>" + element.title + "</div>" }, // omitted for brevity, see the source of this page
						formatSelection: function(element){  return element.title; }, // omitted for brevity, see the source of this page
						dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
						escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
					});

					//Если есть данные о значении, то делаем выбор
					<?php 
						$responsible_id = get_post_meta( $post->ID, 'responsible-cp-posts-sql', true );

						if($responsible_id != ''): ?>   
						$("#cp_case_responsible_input").select2(
							"data", 
							<?php echo json_encode(array('id' => $responsible_id, 'title' => get_the_title($responsible_id))); ?>
						); 
					<?php endif; ?>


				});
			</script>   
		</div>					

		<!-- Срок -->
		<div id="cp_field_date_deadline_div" >
			<?php
			$date_deadline = get_post_meta($post->ID, "deadline_cp", true);
			$value = $date_deadline;
			?>
			<div>
				<label for="cp_field_date_deadline_input" class="cp_forms cp_label" id="cp_field_date_deadline_label">Срок:</label>
			</div>
			<div>
				<input type="text" id="deadline_cp" name="cp_date_deadline" class="cp_full_width cp_input_datepicker" value="<?php echo $value?>"/>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					 rome(deadline_cp, { weekStart: 1 });
				});
			</script>
		</div>
		
		<!-- Дата закрытия -->
		<div id="cp_date_end_wrapper" >
			<?php
			$date_deadline = get_post_meta($post->ID, "cp_date_end", true);
			$value = $date_deadline;
			?>
			<div>
				<label for="cp_date_end" class="cp_forms cp_label" id="cp_field_date_deadline_label">Дата закрытия:</label>
			</div>
			<div>
				<input id="cp_date_end" name="date_end" class="form-control" autocomplete="off" value="<?php  echo get_post_meta( $post->ID, 'cp_date_end', true ); ?>">
			</div> 
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					 rome(cp_date_end, { weekStart: 1 });
				});
			</script>
		</div>						
		
		<!-- Результат -->
		<div id="cp_field_result_div">
			<?php
			$terms = get_the_terms( $post_id, 'results' );
			
			//get first term from array
			if (is_array($terms)) $term = array_shift($terms);
			if (isset($term->term_id)){
				$case_result_id = $term->term_id;
			} else $case_result_id = '0';
			?>

			<div>
				<label for="cp_field_result_select" class="cp_label">Результат</label>
			</div>
			<div>
				<?php
				wp_dropdown_categories( array(
					'name' => 'cp_case_result',
					'class' => 'cp_full_width',
					'id' => 'cp_field_result_select',
					'echo' => 1,
					'hide_empty' => 0, 
					'show_option_none' => 'Без результата',
					'option_none_value' => '0',
					'selected' => $case_result_id,
					'hierarchical' => 1,
					'taxonomy' => 'results'
				));
				?>
			</div>
		</div>						
		
		<!-- Основание дела -->
		<div id="cp_case_post_parent_div">
			<?php
				$case_parent_id = '0';
					
				if ($post->post_parent){
					$case_parent_id = $post->post_parent;
				} elseif (isset($_REQUEST['case_parent_id']) && is_numeric($_REQUEST['case_parent_id'])) {
				   $case_parent_id = $_REQUEST['case_parent_id'];
				} else $case_parent_id = '0';
			?>
			<div>
				<label class="cp_label" id="cp_case_post_parent_input_label" for="cp_case_post_parent_input">Основание</label>
			</div>
			<div id="cp_case_post_parent_edit">
				<input type="hidden" id="case_post_parent_input" name="cp_case_post_parent" class="cp_select2_single" />
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {

					$("#case_post_parent_input").select2({
						placeholder: "Родительская задача",
						width: '100%',
						allowClear: true,
						minimumInputLength: 1,
						ajax: {
							url: "<?php echo admin_url('admin-ajax.php') ?>",
							dataType: 'json',
							quietMillis: 100,
							data: function (term, page) { // page is the one-based page number tracked by Select2
								return {
									action: 'query_posts_cases',
									posts_per_page: 10, // page size
									paged: page, // page number
									s: term //search term
								};
							},
							results: function (data, page) {
									//alert(data.total);
									var more = (page * 10) < data.total; // whether or not there are more results available

									// notice we return the value of more so Select2 knows if more results can be loaded
									return {
										results: data.items,
										more: more
									};
							}
						},
						formatResult: function(element){ return "<div>" + element.title + "</div>" }, // omitted for brevity, see the source of this page
						formatSelection: function(element){  return element.title; }, // omitted for brevity, see the source of this page
						dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
						escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
					});
					
					//Если есть данные о значении, то делаем выбор
					<?php if(!empty($case_parent_id)): ?>   
						$("#case_post_parent_input").select2(
							"data", 
							<?php echo json_encode(array('id' => $case_parent_id, 'title' => get_the_title($case_parent_id))); ?>
						); 
					<?php endif; ?>

				});
			</script>
		</div>

		
		<!-- ОТ: -->
		<div id="cp_case_from_wrapper">
			<div>
				<div>
					<label class="cp_label" id="cp_case_from_label" for="cp_case_from_input" onclick="">От:</label>
				</div>
				<div id="cp_case_from_edit">
					<div id="cp_case_from_edit_input">
						<input type="hidden" id="cp_case_from_input" name="cp_from" class="cp_select2_single" />
					</div>  
				</div>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {

					$("#cp_case_from_input").select2({
						placeholder: "Выберите субъекта",
						width: '100%',
						allowClear: true,
						minimumInputLength: 1,
						ajax: {
								url: "<?php echo admin_url('admin-ajax.php') ?>",
								dataType: 'json',
								quietMillis: 100,
								data: function (term, page) { // page is the one-based page number tracked by Select2
										return {
												action: 'query_from',
												page_limit: 10, // page size
												page: page, // page number
												//params: {contentType: "application/json;charset=utf-8"},
												q: term //search term
										};
								},
								results: function (data, page) {
										//alert(data.total);
										var more = (page * 10) < data.total; // whether or not there are more results available

										// notice we return the value of more so Select2 knows if more results can be loaded
										return {
												results: data.elements,
												more: more
												};
								}
						},
						
						formatResult: function(element){ return "<div>" + element.title + "</div>" }, // omitted for brevity, see the source of this page
						formatSelection: function(element){  return element.title; }, // omitted for brevity, see the source of this page
						dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
						escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
					});

					//Если есть данные о значении, то делаем выбор
					<?php 
						$item_id = get_post_meta( $post->ID, 'cp_from', true );

						if($item_id != ''): ?>   
						$("#cp_case_from_input").select2(
							"data", 
							<?php echo json_encode(array('id' => $item_id, 'title' => get_the_title($item_id))); ?>
						); 
					<?php endif; ?>


				});
			</script>   
		</div>
	
		<!-- Адресат: -->
		<div id="cp_case_to_wrapper">
			<div id="cp_case_to_wrapper">
				<div>
					<div>
						<label class="cp_label" id="cp_case_to_label" for="cp_case_to_input" onclick="">Адресат:</label>
					</div>
					<div id="cp_case_to_edit">
						<div id="cp_case_to_edit_input">
							<input type="hidden" id="cp_case_to_input" name="cp_to" class="cp_select2_single" />
						</div>  
					</div>
				</div>
				<script type="text/javascript">
					jQuery(document).ready(function($) {

						$("#cp_case_to_input").select2({
							placeholder: "Выберите субъекта",
							width: '100%',
							allowClear: true,
							minimumInputLength: 1,
							ajax: {
									url: "<?php echo admin_url('admin-ajax.php') ?>",
									dataType: 'json',
									quietMillis: 100,
									data: function (term, page) { // page is the one-based page number tracked by Select2
											return {
													action: 'query_to',
													page_limit: 10, // page size
													page: page, // page number
													//params: {contentType: "application/json;charset=utf-8"},
													q: term //search term
											};
									},
									results: function (data, page) {
											//alert(data.total);
											var more = (page * 10) < data.total; // whether or not there are more results available

											// notice we return the value of more so Select2 knows if more results can be loaded
											return {
													results: data.elements,
													more: more
													};
									}
							},
							
							formatResult: function(element){ return "<div>" + element.title + "</div>" }, // omitted for brevity, see the source of this page
							formatSelection: function(element){  return element.title; }, // omitted for brevity, see the source of this page
							dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
							escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
						});

						//Если есть данные о значении, то делаем выбор
						<?php 
							$item_id = get_post_meta( $post->ID, 'cp_to', true );

							if($item_id != ''): ?>   
							$("#cp_case_to_input").select2(
								"data", 
								<?php echo json_encode(array('id' => $item_id, 'title' => get_the_title($item_id))); ?>
							); 
						<?php endif; ?>
					});
				</script>   
			</div>
		</div>
		
		<!-- SUBMIT -->
		<br>
		<input class="btn btn-default navbar-btn" type="submit" value="Опубликовать дело"></input>
		


		

			
			
		
</form>

</div>
<?php



?>
