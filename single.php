<?php
/**
 * The Template for displaying all single posts.
 */
get_header();
$layout = of_get_option('homepage_layout');
?>

<?php if($layout === '2col_rev') get_sidebar(); ?>

<div id="content" class="span8" role="main">
	<?php
		while ( have_posts() ) : the_post();
			get_template_part( 'content', 'single' );
			comments_template( '', true );
		endwhile;
	?>
</div><!--#content-->

<?php if($layout === '2col') get_sidebar(); ?>
<?php get_footer(); ?>