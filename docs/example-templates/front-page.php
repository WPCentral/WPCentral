<?php get_header(); ?>

	<?php if ( class_exists( 'WP_Central_Stats' ) ) { ?>
	<div id="content" class="container">

		<h1 id="site-logo"><span>WordPress</span> <?php echo WP_Central_Stats::wp_version(); ?> stats</h1>
		<h2 id="download-counter"><?php echo WP_Central_Stats::wordpress_downloads(); ?> downloads</h2>

		<?php
			echo WP_Central_Graph::get( 'morris', 'line_chart', WP_Central_Stats::downloads_per_day(), array( 'x' => 'date', 'y' => 'count', 'label' => 'Downloads' ) );
		?>

		<div class="row">
			<div class="col-md-6">
				<h2 class="text-center">Last 7 days</h2>
				<?php echo WP_Central_Graph::get( 'morris', 'bar', WP_Central_Stats::downloads_last7days(), array( 'x' => 'label', 'y' => 'value', 'label' => 'Downloads' ) ); ?>
			</div>

			<div class="col-md-6">
				<h2 class="text-center">Releases</h2>

				<div class="list-group">
					<?php
					$releases = WP_Central_Stats::get_minor_releases();
					foreach( $releases as $release ) {
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
				<h2 class="text-center">Downloads per day</h2>
				<?php echo WP_Central_Graph::get( 'chartjs', 'radar_chart', WP_Central_Stats::counts_per_day() ); ?>
			</div>

			<div class="col-md-6">
				<h2 class="text-center">Downloads per hour</h2>
				<?php echo WP_Central_Graph::get( 'chartjs', 'line_chart', WP_Central_Stats::counts_per_hour(), array(
					'options' => array(
						'pointHitDetectionRadius' => 5,
					),
				) ); ?>
				<p class="text-center">All times are GMT based</p>
			</div>
		</div>

	</div>
	<?php } ?>


<?php get_footer(); ?>