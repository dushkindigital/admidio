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
		
		# form fields in reg/etc
		'form_fields' => (object)array(
			'reg' => array(
					'LAST_NAME', 'FIRST_NAME', 'EMAIL',
					
					'L4P_DB_SCHOOL',
					'L4P_DB_MESSAGE'
					
					/*
					'L4P_DB_MEMBERSHIP_TYPE',
					'L4P_DB_MESSAGE',
					'L4P_DB_REFERENCES',
					#'L4P_DB_TEMP_PASSWORD',
					#'L4P_DB_TEMP_PASS_CHANGED',
					#'L4P_DB_TEMP_PASS_EXPIRATION',
					
					'L4P_DB_ADDRESS_2',
					'L4P_DB_EMAIL_2',
					'L4P_DB_EMPLOYER',
					'L4P_DB_PHONE_2',
					'L4P_DB_POSITION',
					'L4P_DB_WEBSITE_2',
					
					'L4P_DB_CLASS_OF',
					'L4P_DB_DEGREE',
					'L4P_DB_MATRICULATION_YEAR',
					'L4P_DB_SCHOOL',
					'L4P_DB_SUBJECT_READ'
					*/
			)
		),
		
		# preference settings
		'preference' => (object)array(
			'registration_mode' => 100
		)
	);
}