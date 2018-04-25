<?php
namespace cantabnyc;

/**
 * get the configuration data
 * @return object Packed data
 */
function get_configs() : \stdClass {
	
	return (object)array(
		
		# list of colleges from wikipedia
		'colleges' => [
			"Christ's",
			"Churchill",
			"Clare",
			"Clare Hall",
			"Corpus Christi",
			"Darwin",
			"Downing",
			"Emmanuel",
			"Fitzwilliam",
			"Girton",
			"Gonville and Caius",
			"Homerton",
			"Hughes Hall",
			"Jesus",
			"King's",
			"Lucy Cavendish",
			"Magdalene",
			"Murray Edwards",
			"Newnham",
			"Pembroke",
			"Peterhouse",
			"Queens'",
			"Robinson",
			"St Catharine's",
			"St Edmund's",
			"St John's",
			"Selwyn",
			"Sidney Sussex",
			"Trinity College",
			"Trinity Hall",
			"Wolfson"
		],
		
		# expiry of regstration (in days)
		'expiry' => 7,
		
		# preference settings
		'preference' => (object)array(
			'registration_mode' => 100
		)
	);
}