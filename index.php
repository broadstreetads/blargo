<?php
/**
 * Default template file.
 * Used in the rare event that none of the other templates handle a particular page type
 */
get_header();
$layout = of_get_option('homepage_layout');
?>

<?php if($layout === '2col_rev') get_sidebar(); ?>

<div id="content" class="stories span8" role="main">
	<?php
		if ( have_posts() ) {
			while ( have_posts() ) : the_post();
				get_template_part( 'content', 'index' );
			endwhile;
			largo_content_nav( 'nav-below' );
		} else {
			get_template_part( 'content', 'not-found' );
		}
	?>
</div><!--#content-->

<?php if($layout === '2col') get_sidebar(); ?>
<?php get_footer(); ?>