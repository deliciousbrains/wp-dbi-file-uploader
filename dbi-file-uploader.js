(function( $, wpmdb ) {

	var reader = {};
	var file = {};
	var slice_size = 1000 * 1024;

	function start_upload( event ) {
		event.preventDefault();
		
		reader = new FileReader();
		file = document.querySelector( '#dbi-file-upload' ).files[0];

		upload_file( 0 );
	}
	$( '#dbi-file-upload-submit' ).on( 'click', start_upload );

	function upload_file( start ) {
		var next_slice = start + slice_size + 1;
		var blob = file.slice( start, next_slice );

		reader.onloadend = function( event ) {
			if ( event.target.readyState !== FileReader.DONE ) {
				return;
			}
			
			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					action: 'dbi_upload_file',
					file_data: event.target.result,
					file: file.name,
					file_type: file.type,
					nonce: dbi_vars.upload_file_nonce
				},
				error: function( jqXHR, textStatus, errorThrown ) {
					console.log( jqXHR, textStatus, errorThrown );
				},
				success: function( data ) {
					var size_done = start + slice_size;
					var percent_done = Math.floor( ( size_done / file.size ) * 100 );
					
					if ( next_slice < file.size ) {
						// Update upload progress
						$( '#dbi-upload-progress' ).html( 'Uploading File - ' + percent_done + '%' );

						// More to upload, call function recursively
						upload_file( next_slice );
					} else {
						// Update upload progress
						$( '#dbi-upload-progress' ).html( 'Upload Complete!' );
					}
				}
			} );
		};

		reader.readAsDataURL( blob );
	}

})( jQuery );
