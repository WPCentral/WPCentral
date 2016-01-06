<?php global $wp_release; ?>

		<h2 id="download-counter">
			<?php printf( __( '%s downloads', 'wpcentral' ), $wp_release->get_download_count() ); ?>
		</h2>

		<?php
			echo WP_Central_Graph::get( 'morris', 'area_chart', $wp_release->downloads_per_day(), array( 'x' => 'date', 'y' => 'count', 'label' => __( 'Downloads', 'wpcentral' ) ) );
		?>

		<p class="text-xs-center p-t-1"><?php _e( 'All times are GMT based', 'wpcentral' ); ?></p>
