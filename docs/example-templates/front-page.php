<?php get_header(); ?>

	<div id="content" class="container">

		<h1 id="site-logo"><span>WordPress</span> <?php echo WordPress_Stats::latest_version(); ?> stats</h1>
		<h2 id="download-counter"><?php echo WordPress_Stats_Api::wordpress_downloads(); ?> downloads</h2>

		<?php
			echo wordpress_stats_graph( 'morris', 'line_chart', WordPress_Stats_Api::downloads_per_day(), array( 'x' => 'date', 'y' => 'count', 'label' => 'Downloads' ) );
		?>

		<div class="row">
			<div class="col-md-6">
				<h2 class="text-center">Last 7 days</h2>
				<?php echo wordpress_stats_graph( 'morris', 'bar', WordPress_Stats_Api::downloads_last7days(), array( 'x' => 'label', 'y' => 'value', 'label' => 'Downloads' ) ); ?>
			</div>

			<div class="col-md-6">
				<h2 class="text-center">Releases</h2>

				<div class="list-group">
					<?php
					$releases = WordPress_Stats_Api::get_minor_releases();
					foreach( $releases as $release ) {
						echo "\t\t\t\t";
						echo '<a href="' . $release['link'] . '" class="list-group-item">';
						echo $release['title'];
						echo '</a>';
					}
					?>
				</div>
			</div>
		</div>


		<div class="row">
			<div class="col-md-6">
				<h2 class="text-center">Downloads per day</h2>
				<?php echo wordpress_stats_graph( 'chartjs', 'radar_chart', WordPress_Stats_Api::counts_per_day() ); ?>
			</div>

			<div class="col-md-6">
				<h2 class="text-center">Downloads per hour</h2>
				<?php echo wordpress_stats_graph( 'chartjs', 'line_chart', WordPress_Stats_Api::counts_per_hour(), array(
					'options' => array(
						'pointHitDetectionRadius' => 5,
					),
				) ); ?>
				<p class="text-center">All times are GMT based</p>
			</div>
		</div>

	</div>


<?php get_footer(); ?>