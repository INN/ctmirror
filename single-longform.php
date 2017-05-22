<?php
/**
 * Template Name: Longform Layout
 * Template Post Type: post
 * Description: Shows the post with a full-width header image
 */
get_header( "longform" );
$series_headline = get_post_meta( $post->ID, '_ctmirror_series_headline', true );
$featured_image = get_the_post_thumbnail_url( $post->ID, "full" );
$header_bg = $featured_image ? ' style="background-image:url(' . $featured_image . ');"' : '';
?>
<nav id="site-navigation" class="lf-sticky" itemscope itemtype="http://schema.org/Organization">
	<div id="longform-logo">
		<a href="//ctmirror.org"><img src="<?php echo bloginfo('stylesheet_directory'); ?>/images/mirrorlogo.png" alt="CT Mirror Logo" /></a>
	</div>
	<div class="longform-social">
		<a href="https://twitter.com/home?status=<?php the_permalink(); ?>">
			<img class="share-logo" src="<?php echo bloginfo('stylesheet_directory'); ?>/images/twitter.png" />
		</a>
		<a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>">
			<img class="share-logo facebook-logo" src="<?php echo bloginfo('stylesheet_directory'); ?>/images/facebook.png" />
		</a>
	</div>
</nav>
<!-- PLEASE ADD REAL FEATURED IMAGE AS BACKGROUND IMAGE HERE -->
<div class="lf-background"<?php echo $header_bg; ?>></div>

<div class="lf-header">
	<div class="abs-center">
		<h2 class="pre-headline"><?php echo $series_headline; ?></h2>
		<h1 class="entry-title big-headline" itemprop="headline"><?php the_title(); ?></h1>
	 	<h5 class="byline big-meta-byline"><?php largo_byline(); ?></h5>
	</div>
</div>

<div id="page" class="hfeed clearfix longform">

<nav class="site-navigation">
	<div id="longform-logo">
		<a href="//ctmirror.org"><img src="<?php echo bloginfo('stylesheet_directory'); ?>/images/mirrorlogo.png" alt="CT Mirror Logo" /></a>
	</div>
	<div class="longform-social">
		<a href="https://twitter.com/home?status=<?php the_permalink(); ?>">
			<img class="share-logo" src="<?php echo bloginfo('stylesheet_directory'); ?>/images/twitter.png" />
		</a>
		<a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>">
			<img class="share-logo facebook-logo" src="<?php echo bloginfo('stylesheet_directory'); ?>/images/facebook.png" />
		</a>
	</div>
</nav>

<header class="print-header">
	<p><strong><?php echo esc_attr( get_bloginfo( 'name' ) ); ?></strong> (<?php echo esc_url( $current_url ); ?>)</p>
</header>

<div id="main" class="row-fluid clearfix">
<div id="content" class="span8" role="main">
	<?php
		while ( have_posts() ) : the_post();
			get_template_part( 'content', 'single' );
			//comments_template( '', true );
			echo do_shortcode( '[fbcomments]' );
		endwhile;
	?>
</div><!--#content-->

<?php get_footer(); ?>
