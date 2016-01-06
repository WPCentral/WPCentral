<?php global $wp_release; ?>

		<h2 id="download-counter">
			<?php printf( __( '%s downloads', 'wpcentral' ), $wp_release->get_download_count() ); ?>
		</h2>

		<?php
			echo WP_Central_Graph::get( 'morris', 'line_chart', $wp_release->downloads_per_day(), array( 'x' => 'date', 'y' => 'count', 'label' => __( 'Downloads', 'wpcentral' ) ) );
		?>

		<div class="row">
			<div class="col-md-6">
				<h2><?php _e( 'Last 7 days', 'wpcentral' ); ?></h2>
				<?php echo WP_Central_Graph::get( 'morris', 'bar', $wp_release->downloads_last7days(), array( 'x' => 'label', 'y' => 'value', 'label' => __( 'Downloads', 'wpcentral' ) ) ); ?>
			</div>

			<div class="col-md-6">
				<h2><?php _e( 'Releases', 'wpcentral' ); ?></h2>

				<div class="list-group">
					<?php
					foreach ( $wp_release->get_releases() as $release ) {
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
				<h2><?php _e( 'Downloads per day', 'wpcentral' ); ?></h2>
				<?php echo WP_Central_Graph::get( 'chartjs', 'radar_chart', $wp_release->counts_per_day() ); ?>
			</div>

			<div class="col-md-6">
				<h2><?php _e( 'Downloads per hour', 'wpcentral' ); ?></h2>
				<?php echo WP_Central_Graph::get( 'chartjs', 'line_chart', $wp_release->counts_per_hour(), array(
					'options' => array(
						'pointHitDetectionRadius' => 5,
					),
				) ); ?>
			</div>
		</div>

		<p class="text-xs-center p-t-1"><?php _e( 'All times are GMT based', 'wpcentral' ); ?></p>
