<?php

class WP_Central_Graph_Morris extends WP_Central_Graph_Abstract {

	public function register_script() {
		wp_register_style( 'morris', plugins_url( 'morris.css', __FILE__ ), array(), '0.5.1' );
		wp_enqueue_style( 'morris' );

		wp_register_script( 'morris', plugins_url( 'morris.min.js', __FILE__ ), array( 'jquery', 'raphael' ), '0.5.1' );
		wp_enqueue_script( 'morris' );
	}

	public function line_chart( $args ) {
		$html  = '<div id="' . $this->unique_id() . '" class="graph"></div>';

		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= 'window.morris_objects = window.morris_objects || [];';
		$html .= 'window.morris_objects.push( Morris.Line({';
		$html .= "element: '" . $this->unique_id() . "',";
		$html .= 'data :' . json_encode( $this->data );


		if ( isset( $args['x'] ) ) {
			$html .= ", xkey: '" . $args['x'] . "'";
		}

		if ( isset( $args['y'] ) ) {
			$html .= ", ykeys: " . json_encode( (array) $args['y'] );
		}

		if ( isset( $args['label'] ) ) {
			$html .= ",labels: " . json_encode( (array) $args['label'] );
		}
		else {
			$html .= ",labels: ['Value']";
		}

		//$html .= ",formatter: function (value, data) { return 'value/100' + '%'; }";

		//$html .= ',hoverCallback: function (index, options, content, row) { return row.y + "%"; }';

		$html .= '}));';
		$html .= '});';
		$html .= '</script>';

		return $html;
	}

	public function area_chart( $args ) {
		$html  = '<div id="' . $this->unique_id() . '" class="graph"></div>';

		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= 'window.morris_objects = window.morris_objects || [];';
		$html .= 'window.morris_objects.push( Morris.Area({';
		$html .= "element: '" . $this->unique_id() . "',";
		$html .= 'data :' . json_encode( $this->data );


		if ( isset( $args['x'] ) ) {
			$html .= ", xkey: '" . $args['x'] . "'";
		}

		if ( isset( $args['y'] ) ) {
			$html .= ", ykeys: " . json_encode( (array) $args['y'] );
		}

		if ( isset( $args['label'] ) ) {
			$html .= ",labels: " . json_encode( (array) $args['label'] );
		}
		else {
			$html .= ",labels: ['Value']";
		}

		$html .= ',pointSize:0';

		if ( isset( $args['ymax'] ) ) {
			$html .= ',ymax:' . esc_js( $args['ymax'] );
		}


		$html .= '}));';
		$html .= '});';
		$html .= '</script>';

		return $html;
	}

	public function pie_chart( $args ) {
		$html  = '<div id="' . $this->unique_id() . '"></div>';

		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= 'window.morris_objects = window.morris_objects || [];';
		$html .= 'window.morris_objects.push( Morris.Donut({';
		$html .= "element: '" . $this->unique_id() . "',";
		$html .= 'data :' . json_encode( $this->data );

		if ( isset( $args['x'] ) ) {
			$html .= ", xkey: '" . $args['x'] . "'";
		}

		if ( isset( $args['y'] ) ) {
			$html .= ", ykeys: ['" . $args['y'] . "']";
		}

		$html .= '}));';
		$html .= '});';
		$html .= '</script>';

		return $html;
	}

	public function bar( $args ) {
		$html  = '<div id="' . $this->unique_id() . '"></div>';

		$html .= '<script type="text/javascript">';
		$html .= 'jQuery(document).ready(function($) {';
		$html .= 'window.morris_objects = window.morris_objects || [];';
		$html .= 'window.morris_objects.push( Morris.Bar({';
		$html .= "element: '" . $this->unique_id() . "',";
		$html .= 'data :' . json_encode( $this->data );


		if ( isset( $args['x'] ) ) {
			$html .= ", xkey: '" . $args['x'] . "'";
		}

		if ( isset( $args['y'] ) ) {
			$html .= ", ykeys: ['" . $args['y'] . "']";
		}

		if ( isset( $args['label'] ) ) {
			$html .= ",labels: ['" . $args['label'] . "']";
		}
		else {
			$html .= ",labels: ['Value']";
		}


		$html .= '}));';
		$html .= '});';
		$html .= '</script>';

		return $html;
	}

}