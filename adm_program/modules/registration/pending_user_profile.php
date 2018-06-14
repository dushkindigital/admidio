<?php

require_once (__DIR__ . '/../../../adm_program/system/common.php');
require_once (__DIR__ . '/../adm_program/system/login_valid.php');

require_once (__DIR__ . '/../adm_program/modules/profile/roles_functions.php');

require_once (__DIR__ . '/engine/bootstrap.php');

/**
 * build the page
 * @param object $datum_user The user data
 */
function build_page ( $datum_user ) {

	# set headline of the script
	$headline = $GLOBALS['gL10n']->get('L4P_PENDING_USER_PROFILE');

	$GLOBALS['gNavigation']->addUrl(CURRENT_URL, $headline);

	// create html page object
	$page = new HtmlPage($headline);

	# show back link
	$profileMenu = $page->getMenu();

	if ($GLOBALS['gNavigation']->count() > 1) {
		$profileMenu->addItem('menu_item_back', $GLOBALS['gNavigation']->getPreviousUrl(), $GLOBALS['gL10n']->get('SYS_BACK'), 'back.png');
	}

	# show data
	$form = new HtmlForm('pending_user_profile_form', null);

	# name
	$form->addStaticControl(
		'NAME',
		'<img alt="profile icon" src="' . ADMIDIO_URL . '/adm_themes/modern/icons/profile.png" /> <span style="font-weight: normal; text-decoration: underline;">' . $datum_user->getValue('FIRST_NAME') . ' ' . $datum_user->getValue('FIRST_NAME') . "</span>",
		$datum_user->getValue('EMAIL')
	);

	# school
	$form->addStaticControl('L4P_DB_SCHOOL', $GLOBALS['gL10n']->get('L4P_DB_SCHOOL'), $datum_user->getValue('L4P_DB_SCHOOL') );

	# message
	$form->addStaticControl('L4P_DB_MESSAGE', $GLOBALS['gL10n']->get('L4P_DB_MESSAGE'), $datum_user->getValue('L4P_DB_MESSAGE') );

	$page->addHtml( $form->show(false) );

	# $page->addCssFile( "adm_program/modules/registration/asset/css/pending_user_profile.min.css" );

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

	// Testen ob Recht besteht Profil einzusehn
	if (!$GLOBALS['gCurrentUser']->hasRightViewProfile($datum_user)) {
		$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_NO_RIGHTS'));
		// => EXIT
	}

	if (\sizeof($_POST) > 0) {
		# POST request
		handle_request_post( $organisation_id, $datum_user );

	} else {
		# GET request
		build_page( $datum_user );
	}
}

/**
 * handle the form POST
 */
function handle_request_post ( $organisation_id, $datum_user ) {


}

# start up - handle the GET/POST requests
handle_request();
