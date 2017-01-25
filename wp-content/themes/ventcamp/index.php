<?php
/*
Main Blog page template
*/

get_header();

?>

	<div class="container">
		<div class="content col-md-9 col-sm-12">
			<main>

				<?php
					if ( have_posts() ) :

						if ( is_home() && ! is_front_page() ) : ?>
							<header>
								<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
							</header>

						<?php
						endif;

						while ( have_posts() ) : the_post();

							/*
							 * Include the Post-Format-specific template for the content.
							 * If you want to override this in a child theme, then include a file
							 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
							 */
							get_template_part( 'content', get_post_format() );

						endwhile;

						// Init our pagination class
						$pagination = new Pagination();
						// Output the resulted links
						$pagination->get_links();
						
					else :

						get_template_part( 'content', 'none' );

					endif;
				?>

			</main>
		</div>

		<div class="col-md-3 col-sm-12 sidebar-right">
			<?php get_sidebar(); ?>
		</div>

	</div>
<?php get_footer(); ?>
