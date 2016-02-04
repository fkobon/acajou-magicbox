<?php

/*

Template Name: Advanced Search Demo



A custom page template to demonstrate the functionality of WP Advanced Search.

To use, simply create a new page and select "Advanced Search Demo" under

Page Attributes > Template.

*/

define('WPAS_DEBUG', false);

get_header(); 

?>



	<div class="page-body">

		<h1><?php the_title(); ?></h1>



		<?php 

			$order ='date';

			$args = array();



			$args['wp_query'] = array('post_type' => 'kado',

			                                'posts_per_page' => 6,

			                                'order' => 'DESC',

			                                'orderby' =>$order);



			$my_search_object = new WP_Advanced_Search($args);



			$my_search_object->the_form();



			$temp_query = $wp_query;

			$wp_query = $my_search_object->query();



			if ( have_posts() ): 



				while ( have_posts() ): the_post();

				?>

					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

					<p><strong>Author:</strong> <?php the_author();?> &nbsp;&nbsp; <strong>Date:</strong> <?php the_date();?></p>

					<?php the_excerpt(); ?>

					<p><a href="<?php the_permalink(); ?>">Read more...</a></p>

				<?php

				endwhile; 



			$my_search_object->pagination();



			else :



				echo 'Sorry, no posts matched your criteria.';



			endif;

			

			$wp_query = $temp_query;

			wp_reset_query();

		?>



	</div><!-- /. page-body -->



<?php get_footer(); ?>