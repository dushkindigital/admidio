<?php
namespace cantabnyc;

/**
 * auto generate a password
 * @param int $length The length of the password to generate
 * @return string
 */
function auto_generate_password (int $length) : string {
	
	$result = \random_bytes( $length );
	
	$result = \base64_encode( $result );
	
	$result = \substr( $result, 0, $length );
	
	return $result;
}
