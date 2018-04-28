<?php

require_once (__DIR__ . '/../adm_program/system/common.php');

require_once (__DIR__ . '/engine/bootstrap.php');

/**
 * build the page
 */
function build_page () {
	
	# set headline of the script
	$headline = $GLOBALS['gL10n']->get('L4P_PENDING_USER_PROFILE');
	
	$GLOBALS['gNavigation']->addUrl(CURRENT_URL, $headline);
	
	// create html page object
	$page = new HtmlPage($headline);
	
	$page->hideMenu();
	
	$html =<<<EOD
<p>Thank you for registering. We will contact you shortly</p>
EOD;
	
	$page->addHtml( $html );
	
	$page->addCssFile( "l4p/asset/css/handle_password_reset.min.css" );
	
	$page->show();
}

/**
 * handle the request
 */
function handle_request () {
	
	# check permissions
	if ( $GLOBALS['gPreferences']['registration_mode'] == 0) {
		$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_MODULE_DISABLED'));
		// => EXIT
	}
	
	# Initialize and check the parameters
	$get_user_id = admFuncVariableIsValid($_GET, 'user_id',  'int');
	
	# org
	$organisation_id = $GLOBALS['gCurrentOrganization']->getValue('org_id');
	
	# read user data
	$datum_user = new User($GLOBALS['gDb'], $GLOBALS['gProfileFields'], $get_user_id);
	
	if (\sizeof($_POST) > 0) {
		
		handle_request_post( $organisation_id, $datum_user );
		
	} else {
		
		build_page();
	}
	
	
}

/**
 * handle the form POST
 */
function handle_request_post ( $organisation_id, $datum_user ) {
	
	
}

###
handle_request();
