<?php
get_header();

$current_version  = WP_Central_Stats::wp_version();
$selected_version = get_query_var('wp_version_selector') ?: $current_version;
?>

	<?php if ( class_exists( 'WP_Central_Stats' ) ) { ?>
	<div class="version-header bg-info p-y-1">
		<div class="container">
			<div class="row">
				<div class="col-sm-9">
					<h1>
						<?php printf( __( '%s %s stats', 'wpcentral' ), '<span>WordPress</span>', $selected_version ); ?>
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

		<h2 id="download-counter">
			<?php printf( __( '%s downloads', 'wpcentral' ), WP_Central_Stats::wordpress_downloads( $selected_version ) ); ?>
		</h2>

		<?php
			echo WP_Central_Graph::get( 'morris', 'line_chart', WP_Central_Stats::downloads_per_day( $selected_version ), array( 'x' => 'date', 'y' => 'count', 'label' => __( 'Downloads', 'wpcentral' ) ) );
		?>

		<div class="row">
			<div class="col-md-6">
				<h2 class="text-center"><?php _e( 'Last 7 days', 'wpcentral' ); ?></h2>
				<?php echo WP_Central_Graph::get( 'morris', 'bar', WP_Central_Stats::downloads_last7days( $selected_version ), array( 'x' => 'label', 'y' => 'value', 'label' => __( 'Downloads', 'wpcentral' ) ) ); ?>
			</div>

			<div class="col-md-6">
				<h2 class="text-center"><?php _e( 'Releases', 'wpcentral' ); ?></h2>

				<div class="list-group">
					<?php
					$releases = WP_Central_Stats::get_minor_releases( $selected_version );
					foreach ( $releases as $release ) {
						echo "\t\t\t\t";
						echo '<a href="' . $release->link . '" class="list-group-item">';
						echo $release->title;
						echo '</a>';
					}
					?>
				</div>
			</div>
		</div>


		<div class="row">
			<div class="col-md-6">
				<h2 class="text-center"><?php _e( 'Downloads per day', 'wpcentral' ); ?></h2>
				<?php echo WP_Central_Graph::get( 'chartjs', 'radar_chart', WP_Central_Stats::counts_per_day( $selected_version ) ); ?>
			</div>

			<div class="col-md-6">
				<h2 class="text-center"><?php _e( 'Downloads per hour', 'wpcentral' ); ?></h2>
				<?php echo WP_Central_Graph::get( 'chartjs', 'line_chart', WP_Central_Stats::counts_per_hour( $selected_version ), array(
					'options' => array(
						'pointHitDetectionRadius' => 5,
					),
				) ); ?>
			</div>
		</div>

		<p class="text-xs-center p-t-1"><?php _e( 'All times are GMT based', 'wpcentral' ); ?></p>

	</div>
	<?php } ?>


<?php get_footer(); ?>