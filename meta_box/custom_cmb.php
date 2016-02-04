<?php
add_action( 'add_meta_boxes', 'my_custom_meta_box_add' );

function my_custom_meta_box_add()
{

$post_types=get_post_types(array('public'=>true,'_builtin'=>false));
// array_push($post_types,'post','page');
    foreach( $post_types as $post_type )
    {
        add_meta_box(
            'custom_meta_box', // $id
            'Details', // $title 
            'my_custom_meta_box_cb', // $callback
             $post_type,
            'normal', // $context
            'high' // $priority
        );
    }
	
}
	// add_meta_box( 'age_range', 'Informations du Kado', 'my_custom_meta_box_cb', $post_types, 'normal', 'high' );

function my_custom_meta_box_cb( $post )
{
	// $values = get_post_custom( $post->ID );

	// $text = isset( $values['my_meta_box_text'] ) ? esc_attr( $values['my_meta_box_text'][0] ) : '';
	// $check = isset( $values['my_meta_box_check'] ) ? esc_attr( $values['my_meta_box_check'][0] ) : '';
	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
	?>
<!-- Age field -->
	<div class="age">
		<?php
		$age_value = get_post_meta($post->ID, 'age_range', true);
		$age = isset($age_value) ? esc_attr( $age_value) : '';

		?>
		<label for="age_meta">Choisissez la tranche d'Age</label>
		<?php
		$age_range = explode( '/', get_option_tree( 'age_range' ));
		echo '<select name="age_range" id="age_range">';

		for( $i=0;$i<count($age_range);$i++)
		{
			// echo '<option value="' . $age_range[$i] . '" selected>' .$age_range[$i]. '</option>';
				echo '<option value="', $age_range[$i], '"', selected( $age, $age_range[$i] ), '>',$age_range[$i], '</option>';
				
				// '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
		}
		echo '</select>';
			?>
	</div>
	<br>
<!-- Sex field -->
	<div class="sex">
		<?php
		$sex_value = get_post_meta($post->ID, 'sex', true);
		$sex_value = isset($sex_value) ? esc_attr( $sex_value) : '';

		?>
		<label for="sex">Choisissez le Sexe</label>
		<?php
		$sex = explode( '/', get_option_tree( 'sex' ));
		echo '<select name="sex" id="sex">';

		for( $i=0;$i<count($sex);$i++)
		{
			echo '<option value="', $sex[$i], '"', selected( $sex_value, $sex[$i] ), '>',$sex[$i], '</option>';
		}
		echo '</select>';
			?>
	</div>
	<br>
<!-- Events field -->
	<div class="events">
		<?php
		$events_value = get_post_meta($post->ID, 'events', true);
		$events_value = isset($events_value) ? esc_attr( $events_value) : '';

		?>
		<label for="events">Choisissez l'Evenement</label>
		<?php
		$events = explode( '/', get_option_tree( 'events' ));
		echo '<select name="events" id="events">';

		for( $i=0;$i<count($events);$i++)
		{
			echo '<option value="', $events[$i], '"', selected( $events_value, $events[$i] ), '>',$events[$i], '</option>';
		}
		echo '</select>';
			?>
	</div>
	<br>
<!-- Price field -->
	<div class="price_range">
		<?php
		$price_range_value = get_post_meta($post->ID, 'price_range', true);
		$price_range_value = isset($price_range_value) ? esc_attr( $price_range_value) : '';

		?>
		<label for="price_range">Choisissez La tranche de Prix</label>
		<?php
		$price_range = explode( '/', get_option_tree( 'price_range' ));
		echo '<select name="price_range" id="price_range">';

		for( $i=0;$i<count($price_range);$i++)
		{
			echo '<option value="', $price_range[$i], '"', selected( $price_range_value, $price_range[$i] ), '>',$price_range[$i], '</option>';
		}
		echo '</select>';
			?>
	</div>
<br>	
<!-- Kado field -->
	<div class="type_kado">
		<?php
		$type_kado_value = get_post_meta($post->ID, 'type_kado', true);
		$type_kado_value = isset($type_kado_value) ? esc_attr( $type_kado_value) : '';

		?>
		<label for="type_kado">Choisissez le Type de Kado</label>
		<?php
		$type_kado = array('Kado','Conseils');
		echo '<select name="type_kado" id="type_kado">';

		for( $i=0;$i<count($type_kado);$i++)
		{
			echo '<option value="', $type_kado[$i], '"', selected( $type_kado_value, $type_kado[$i] ), '>',$type_kado[$i], '</option>';
		}
		echo '</select>';
			?>
	</div>
		

	<?php	
}


add_action( 'save_post', 'age_meta_box_save' );
function age_meta_box_save( $post_id )
{
	// Bail if we're doing an auto save
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	// if our nonce isn't there, or we can't verify it, bail
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;
	
	// if our current user can't edit this post, bail
	if( !current_user_can( 'edit_post' ) ) return;
	
	// now we can actually save the data
	$allowed = array( 
		'a' => array( // on allow a tags
			'href' => array() // and those anchords can only have href attribute
		)
	);
	
	// Probably a good idea to make sure your data is set
	// if( isset( $_POST['my_meta_box_text'] ) )
		// update_post_meta( $post_id, 'my_meta_box_text', wp_kses( $_POST['my_meta_box_text'], $allowed ) );
		
	if( isset( $_POST['age_range'] ) )
		update_post_meta( $post_id, 'age_range', esc_attr( $_POST['age_range'] ) );
	if( isset( $_POST['sex'] ) )
		update_post_meta( $post_id, 'sex', esc_attr( $_POST['sex'] ) );
	if( isset( $_POST['events'] ) )
		update_post_meta( $post_id, 'events', esc_attr( $_POST['events'] ) );
	if( isset( $_POST['price_range'] ) )
		update_post_meta( $post_id, 'price_range', esc_attr( $_POST['price_range'] ) );
	if( isset( $_POST['type_kado'] ) )
		update_post_meta( $post_id, 'type_kado', esc_attr( $_POST['type_kado'] ) );
										
}