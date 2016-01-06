<?php
get_header();

$current_version  = WP_Central_Stats::wp_version();
$selected_version = get_query_var('wp_version_selector') ?: $current_version;

// Get release data
$wp_release = WP_Central_Stats::get_release_data( $selected_version );
?>

	<?php if ( class_exists( 'WP_Central_Stats' ) ) { ?>
	<div class="version-header bg-info p-y-1">
		<div class="container">
			<div class="row">
				<div class="col-sm-9">
					<h1>
						<?php echo $wp_release->get_title(); ?>
					</h1>
				</div>

				<div class="col-sm-3">
					<button class="btn btn-secondary dropdown-toggle pull-sm-right m-x-auto" id="version-dropdown" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?php _e( 'Previous releases', 'wpcentral' ); ?>
					</button>
					<div class="dropdown-menu" aria-labelledby="version-dropdown">
						<?php
						foreach ( WP_Central_Stats::get_major_releases() as $release ) {
							if ( $release == $current_version ) {
								continue;
							}

							echo '<a class="dropdown-item" href="' . esc_url( home_url( '/version/' . $release ) ) . '">' . $release . '</a>' . PHP_EOL;
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div id="content" class="container">

		<?php
		if ( $current_version == $selected_version ) {
			get_template_part( 'front-page-home' );
		}
		else {
			get_template_part( 'front-page-version' );
		}
		?>

	</div>
	<?php } ?>


<?php get_footer(); ?>