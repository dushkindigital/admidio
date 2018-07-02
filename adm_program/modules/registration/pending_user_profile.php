<?php

require_once (__DIR__ . '/../../../adm_program/system/common.php');
require_once (__DIR__ . '/../../system/login_valid.php');

require_once (__DIR__ . '/../../modules/profile/roles_functions.php');

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
	// Content presentation: starts here
	$registeredUserId = $datum_user->getValue('usr_id');
    $fetchMembershipTypeQuery = "SELECT membership_type FROM ".TBL_APPLICATIONS." WHERE uapp_usr_id = $registeredUserId";

	$fetchMembershipType = $GLOBALS['gDb']->query($fetchMembershipTypeQuery);
	$fetchMembershipType = $fetchMembershipType->fetch();
    $membershipType = $fetchMembershipType['membership_type'];
    $memberName = $datum_user->getValue('FIRST_NAME').' '.$datum_user->getValue('LAST_NAME');
    $form->addStaticControl('LABEL_NAME', $GLOBALS['gL10n']->get('LABEL_NAME'),  $memberName);
    $form->addStaticControl('L4P_DB_EMAIL_2', $GLOBALS['gL10n']->get('L4P_DB_EMAIL_2'), $datum_user->getValue('EMAIL') );
    $form->addStaticControl('L4P_DB_MEMBERSHIP_TYPE', $GLOBALS['gL10n']->get('L4P_DB_MEMBERSHIP_TYPE'), $membershipType);

	if( !empty($membershipType) && $membershipType == 'member' ) {
        # school

        $form->addStaticControl('L4P_DB_SCHOOL', $GLOBALS['gL10n']->get('L4P_DB_SCHOOL'), $datum_user->getValue('SCHOOL') );
        $form->addStaticControl('L4P_DB_MATRICULATION_YEAR', $GLOBALS['gL10n']->get('L4P_DB_MATRICULATION_YEAR'), $datum_user->getValue('MATRICULATION_YEAR') );

	} elseif( !empty($membershipType) && $membershipType == 'associate' ) {
        # message
        $associateQuery = "SELECT
                        reference,
                        reference_2
                    FROM
                        adm_user_applications
                    WHERE
                        uapp_usr_id = $registeredUserId";

        $associate = $GLOBALS['gDb']->query($associateQuery);
        $associate = (object) $associate->fetch();
        $form->addStaticControl('L4P_DB_MESSAGE', $GLOBALS['gL10n']->get('L4P_DB_MESSAGE'), $datum_user->getValue('L4P_DB_MESSAGE') );
        $form->addStaticControl('LABEL_REFERENCE', $GLOBALS['gL10n']->get('LABEL_REFERENCE'), $associate->reference );
        $form->addStaticControl('LABEL_REFERENCE_2', $GLOBALS['gL10n']->get('LABEL_REFERENCE_2'), $associate->reference_2 );
		// Reference 1 (Does Not Appear In Profile)
		// Reference 2 (Does Not Appear In Profile)
		// Message (Does Not Appear In Profile)

    }
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
