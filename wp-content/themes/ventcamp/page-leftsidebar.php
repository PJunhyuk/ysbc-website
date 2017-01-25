<?php
/*
Template Name: Sidebar Left
*/

get_header();

?>

<div class="container">
	<div class="col-md-3 col-sm-12 sidebar-left">
		<?php get_sidebar(); ?>
	</div>
	<div class="content col-md-9 col-sm-12">
		<main role='main'>
			<?php
			while ( have_posts() ) : the_post();

				get_template_part( 'content', 'page' );

				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile;
			?>
		</main>
	</div>
</div>

<?php get_footer(); ?>
