<?php
namespace cantabnyc;

/**
 * auto generate a password
 * @param int $length The length of the password to generate
 * @return string
 */
function auto_generate_password (int $length = 8) : string {
	
	$result = \random_bytes( $length + 4); # add some more bytes so base64 delimit chars arent visible
	
	$result = \base64_encode( $result );
	
	$result = \str_replace('+', '', $result );
	$result = \str_replace('=', '', $result );
	
	$result = \substr( $result, 0, $length );
	
	return $result;
}

/**
 * extract the username from an email
 * @param string $email 
 * @return string
 */
function extract_username_from_email (string $email) : string {
	
	$result = \explode( '@', $email );
	$result = $result[0];
	
	return $result;
}

/**
 * tie the form field IDs to a name
 * @param string $email 
 * @return string
 */
function extract_named_form_fields (string $email) : string {
	
	$result = \explode( '@', $email );
	$result = $result[0];
	
	return $result;
}
