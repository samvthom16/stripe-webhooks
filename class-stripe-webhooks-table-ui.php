<?php

class STRIPE_WEBHOOKS_TABLE_UI extends STRIPE_WEBHOOKS_BASE{

	function slugify( $text ){
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);
		if ( empty( $text ) ) {
			return 'n-a';
		}
		return $text;
	}

	function displayRow( $row, $classes = 'grid-list', $column_tag = 'td' ){
		_e( "<tr class='$classes'>" );
		foreach( $row as $col_slug => $col_label ){
			_e( "<$column_tag class='$col_slug'>");
			$col_label = apply_filters( 'stripe_webhooks_admin_column', $col_label, $row, $col_slug );
			_e( $col_label );
			_e( "</$column_tag>" );
		}
		_e( "</tr>" );
	}

	function displayHeaderRow( $columns ){
		$header_row = array( 'number'	=> '#' );
		foreach( $columns as $column ){
			$label = $column['label'];
			$slug = $this->slugify( $label );
			$header_row[ $slug ] = $label;
		}
		_e( "<thead>" );
		$this->displayRow( $header_row, 'grid-list meta', 'th' );
		_e( "</thead>" );
		return $header_row;
	}

	function findChild( $data, $key_str ){
		$key_arr = explode( '->', $key_str );
		$value = $data;
		foreach( $key_arr as $key ){
			$value = isset( $value->$key ) ?  $value->$key : '';
		}
		return $value;
	}

	function checkIsAValidDate($myDateString){
		return (bool)strtotime($myDateString);
	}

	function array_flatten( $array ) {
  	if ( !is_array( $array ) ) return FALSE;
  	$result = array();
  	foreach ($array as $key => $value) {
			array_push( $result, $value );
		}
  	return $result;
	}

	function display( $columns, $data, $filename = 'test' ){
		$csvdata = array();

		_e( '<p><button type="button" class="button" data-behaviour="btn-export-csv">Export CSV</button></p>' );

		_e( "<table class='widefat striped table-view-list posts'>" );

		$header_row = $this->displayHeaderRow( $columns );
		array_push( $csvdata, $this->array_flatten( $header_row ) );

		_e( "<tbody>" );
		$i = 1;
		foreach( $data as $list ){
			$row = array( 'number' => $i );
			foreach( $columns as $column ){
				$slug = $this->slugify( $column['label'] );
				$row[ $slug ] = apply_filters( 'stripe_webhooks_find_child', $this->findChild( $list, $column['key'] ), $slug );
			}
			$this->displayRow( $row );

			array_push( $csvdata, $this->array_flatten( $row ) );
			//array_push( $csvdata, $row );

			$i++;
		}
		_e( "</tbody>" );
		_e( "</table>" );


		$this->browserData( 'csvdata', array(
			'settings'	=> array(
				'nonce'			=> wp_create_nonce( 'stripe-webhooks' ),
				'ajax_url'	=> admin_url( 'admin-ajax.php?action=exportcsv' ),
				'filename'	=> $filename
			),
			'rows'		=> $csvdata
		) );

	}

	function browserData( $type, $data ){
		?>
		<script type="text/javascript">
		if( window.browserData === undefined || window.browserData[ '<?php _e( $type );?>' ] === undefined ){
			var data = window.browserData = window.browserData || {};
			browserData[ '<?php _e( $type );?>' ] = <?php echo json_encode( wp_unslash( $data ) );?>;
		}
		</script>
		<?php
	}

	function pagination( $per_page, $total, $get_params ){

		$currentUrl = admin_url(	'admin.php?page=' . $_GET['page'] );
		foreach( $get_params as $get_param ){
			if( isset( $_GET[ $get_param ] ) ){
				$currentUrl .= "&" . $get_param . "=" . $_GET[ $get_param ];
			}
		}

		$activepage = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;

		$pages = ceil( $total / $per_page );

		if( $pages > 1 ){
			_e( '<ul class="paginate-btns">' );
			for( $i = 1; $i <= $pages; $i++ ){
				$url = $currentUrl . "&paged=" . $i;
				$class = 'button';
				if( $i == $activepage ){ $class .= ' active'; }
				_e("<li><a href='$url' class='$class'>$i</a></li>");
			}
			_e( '</ul>' );
		}



	}


}

STRIPE_WEBHOOKS_TABLE_UI::getInstance();
