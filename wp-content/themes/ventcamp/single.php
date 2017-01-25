<?php
/*
Single post page template
*/

get_header();

?>

	<div class="container">
		<div class="content col-md-9 col-sm-12">
			<main role="main">

			<?php
			while ( have_posts() ) : the_post();

				get_template_part( 'content', get_post_format() );
				
				wp_link_pages();

				// Display links on the next and previous posts
				ventcamp_post_navigation();

				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile;
			?>

			</main>
		</div>

		<div class="col-md-3 col-sm-12 sidebar-right">
			<?php get_sidebar(); ?>
		</div>

	</div>

<?php
get_footer();
