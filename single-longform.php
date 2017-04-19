<?php
/**
 * Template Name: Longform Layout
 * Template Post Type: post
 * Description: Shows the post with a full-width header image
 */
get_header( "longform" );
$featured_image = get_the_post_thumbnail_url();
$header_bg =  $featured_image ? ' style="background-image:url(' . $featured_image . ');"' : '';
?>
<!-- PLEASE ADD REAL FEATURED IMAGE AS BACKGROUND IMAGE HERE -->
<div class="lf-background"<?php echo $header_bg; ?>></div>

<div class="lf-header">
	<div class="abs-center">
		<h1 class="entry-title big-headline" itemprop="headline"><?php the_title(); ?></h1>
	 	<h5 class="byline big-meta-byline"><?php largo_byline(); ?></h5>
	</div>
</div>

<div id="page" class="hfeed clearfix longform">


<?php include( "nav-longform.php" ); ?>

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
