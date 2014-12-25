<?php

/**
 * Chart.js graphs
 */
class WP_Central_Graph_Chartjs extends WP_Central_Graph_Abstract {

	public function register_script() {

		wp_register_script( 'chartjs', plugins_url( 'chart.min.js', __FILE__ ), array(), '1.0.1-beta.4' );
		wp_enqueue_script( 'chartjs' );
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function line_chart( $args ) {

		$data = array(
			'labels'   => wp_list_pluck( $this->data, 'label' ),
			'datasets' => array(
				array(
					'data'      => wp_list_pluck( $this->data, 'value' ),
					'fillColor' => 'rgba(11,98,164,0.5)',
				),
			),
		);

		return $this->get_chart( 'Line', $data, $args );
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function doughnut_chart( $args ) {
		$data = array_reverse( $this->data );

		$args['options'] = array(
			'tooltipTemplate' => "<%= label %> — <%= value %>%",
			'animateRotate'   => false,
		);

		$colors = array(
			'#2ECC40',
			'#41BF3F',
			'#54B23E',
			'#67A63D',
			'#7A993C',
			'#8D8C3B',
			'#A0803A',
			'#B37339',
			'#C66638',
			'#D95A37',
			'#EC4D36',
			'#FF4136',
		);

		foreach ( $data as $key => $entry ) {
			$data[ $key ]          = (array) $entry;
			$data[ $key ]['color'] = next( $colors );
		}

		return $this->get_chart( 'Doughnut', $data, $args );
	}

	/**
	 * @param array $args
	 */
	public function pie_chart( $args ) {
	}

	/**
	 * @param array $args
	 */
	public function bar( $args ) {
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function radar_chart( $args ) {

		$data = array(
			'labels'   => wp_list_pluck( $this->data, 'label' ),
			'datasets' => array(
				array(
					'data'      => wp_list_pluck( $this->data, 'value' ),
					'fillColor' => 'rgba(11,98,164,0.5)',
				),
			),
		);

		return $this->get_chart( 'Radar', $data, $args );
	}

	/**
	 * @param string $type
	 * @param array  $args
	 *
	 * @return string
	 */
	protected function get_chart( $type, $data, $args ) {
		$uid  = esc_attr( $this->unique_id() );
		$type = esc_js( $type );
		$html = '<canvas id="' . $uid . '" class="graph"></canvas>';

		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';

		$options = array(
			'tooltipTemplate' => "<%= Math.floor(value/1000000) %> M",
			'responsive'      => true,
		);

		if ( isset( $args['options'] ) ) {
			$options = array_merge( $options, $args['options'] );
		}

		$html .= 'var data' . $uid . ' = ' . json_encode( $data ) . ";\n";
		$html .= 'var ctx' . $uid . ' = document.getElementById("' . $uid . '").getContext("2d");' . "\n";
		$html .= 'var ' . $type . 'Chart' . $uid . ' = new Chart(ctx' . $uid . ').' . $type . '(data' . $uid . ',' . json_encode( $options ) . ');' . "\n";
		$html .= '});';
		$html .= '</script>';

		return $html;
	}

}
