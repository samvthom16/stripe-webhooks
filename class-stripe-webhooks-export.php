<?php

class STRIPE_WEBHOOKS_EXPORT extends STRIPE_WEBHOOKS_BASE{

	// RETURNING THE FILE PATH WHICH EXISTS IN THE WP UPLOADS DIRECTORY
	function getFilePath( $file_slug ){
		$file = "$file_slug.csv";
		$filePath = array();
		$path = wp_upload_dir();
		$filePath['path'] 	= $path['path'] . "/$file";
		$filePath['url'] 	= $path['url'] . "/$file";
		return $filePath;
	}

	function addRowToCSV( $file_slug, $row, $write_flag = 'a' ){
		$path = $this->getFilePath( $file_slug );
		$outstream = fopen( $path['path'], $write_flag );
		fputcsv( $outstream, $row );
		fclose( $outstream );
	}

	// APPENDS THE ROW OF DATA TO AN ALREADY EXISTING FILE
	function addRowsToCSV( $file_slug, $rows ){

		$path = $this->getFilePath( $file_slug );
		$i = 0;
		foreach( $rows as $row ){
			$outstream = fopen( $path['path'], 'a' );
			if( !$i ){
				$outstream = fopen( $path['path'], 'w' );
			}
			fputcsv( $outstream, $row );
			$i++;
		}

		fclose( $outstream );

		return $path;
	}


}
