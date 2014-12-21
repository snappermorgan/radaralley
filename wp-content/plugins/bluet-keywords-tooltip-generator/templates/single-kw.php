<?php
/**
 * single page template for keywords post type
 */

get_header(); ?>

<style>
#bluet_post_meta{
	text-align: center;
}
</style>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

			<header>
			<h1><?php the_post_thumbnail(); ?></h1>
			</header>
			<div id="bluet_the_article">
				<h1><?php the_title(); ?></h1>
				<div id="bluet_the_content"><?php the_content(); ?></div>	
			</div>
				
			<?php endwhile; ?>

	
		</div><!-- #content -->

	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
