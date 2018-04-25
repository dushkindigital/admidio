<?php

require_once (__DIR__ . '/../adm_program/system/common.php');

require_once (__DIR__ . '/engine/bootstrap.php');

if ( $GLOBALS['gPreferences']['registration_mode'] == 0) {
	$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_MODULE_DISABLED'));
	// => EXIT
}

# org
$organisation_id = $GLOBALS['gCurrentOrganization']->getValue('org_id');

/**
 * handle the form POST
 */
function handle_form_post () {
	
	$user = new UserRegistration( $GLOBALS['gDb'],  $GLOBALS['gProfileFields'], 0);
	$user->setOrganization( $organisation_id );
	
	# ensure fields set
	if ($_POST['usr_login_name'] === '') {
		$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_FIELD_EMPTY', $GLOBALS['gL10n']->get('SYS_USERNAME')));
		// => EXIT
	}
	
	if ($_POST['usr_password'] === '') {
		$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_FIELD_EMPTY', $GLOBALS['gL10n']->get('SYS_PASSWORD')));
		// => EXIT
	}
	
	// Passwort muss mindestens 8 Zeichen lang sein
	if (strlen($_POST['usr_password']) < PASSWORD_MIN_LENGTH) {
		$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('PRO_PASSWORD_LENGTH'));
		// => EXIT
	}

	// beide Passwortfelder muessen identisch sein
	if ($_POST['usr_password'] !== $_POST['password_confirm']) {
		$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('PRO_PASSWORDS_NOT_EQUAL'));
		// => EXIT
	}
	
	if (PasswordHashing::passwordStrength($_POST['usr_password'], $user->getPasswordUserData()) < $gPreferences['password_min_strength']) {
		$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('PRO_PASSWORD_NOT_STRONG_ENOUGH'));
		// => EXIT
	}
	
	// nun alle Profilfelder pruefen
	foreach($gProfileFields->mProfileFields as $field) {
		
			$post_id = 'usf-'. $field->getValue('usf_id');
			
			// check and save only fields that aren't disabled
			if ($field->getValue('usf_disabled') == 0
			|| ($field->getValue('usf_disabled') == 1 && $gCurrentUser->hasRightEditProfile($user, false))
			|| ($field->getValue('usf_disabled') == 1 && $getNewUser > 0))
			{
					if(isset($_POST[$post_id]))
					{
							// Pflichtfelder muessen gefuellt sein
							// E-Mail bei Registrierung immer !!!
							if((strlen($_POST[$post_id]) === 0 && $field->getValue('usf_mandatory') == 1)
							|| (strlen($_POST[$post_id]) === 0 && $field->getValue('usf_name_intern') === 'EMAIL' && $getNewUser === 2))
							{
									$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_FIELD_EMPTY', $field->getValue('usf_name')));
									// => EXIT
							}
							
							// Wert aus Feld in das User-Klassenobjekt schreiben
							$returnCode = $user->setValue($field->getValue('usf_name_intern'), $_POST[$post_id]);
	
							// Ausgabe der Fehlermeldung je nach Datentyp
							if(!$returnCode)
							{
									switch ($field->getValue('usf_type'))
									{
											case 'CHECKBOX':
													$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_INVALID_PAGE_VIEW'));
													// => EXIT
													break;
											case 'DATE':
													$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_DATE_INVALID', $field->getValue('usf_name'), $gPreferences['system_date']));
													// => EXIT
													break;
											case 'EMAIL':
													$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_EMAIL_INVALID', $field->getValue('usf_name')));
													// => EXIT
													break;
											case 'NUMBER':
											case 'DECIMAL':
													$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('PRO_FIELD_NUMERIC', $field->getValue('usf_name')));
													// => EXIT
													break;
											case 'PHONE':
													$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_PHONE_INVALID_CHAR', $field->getValue('usf_name')));
													// => EXIT
													break;
											case 'URL':
													$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_URL_INVALID_CHAR', $field->getValue('usf_name')));
													// => EXIT
													break;
									}
							}
					}
					else
					{
							// Checkboxen uebergeben bei 0 keinen Wert, deshalb diesen hier setzen
							if($field->getValue('usf_type') === 'CHECKBOX')
							{
									$user->setValue($field->getValue('usf_name_intern'), '0');
							}
							elseif($field->getValue('usf_mandatory') == 1)
							{
									$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_FIELD_EMPTY', $field->getValue('usf_name')));
									// => EXIT
							}
					}
			}
	}
	
	# add in the state and expiry fields
	$user->setValue( 'L4P_DB_STATE_REG', 'pending' );
	$user->setValue( 'L4P_DB_EXPIRES',   \date('Y-m-d', \time() + \cantabnyc\get_configs()->expiry*24*60*60) );
	
	/*------------------------------------------------------------*/
	// Save user data to database
	/*------------------------------------------------------------*/
	$GLOBALS['gDb']->startTransaction();
	
	try {
		$user->save();
		
	} catch(AdmException $e) {
		
		unset($_SESSION['profile_request']);
		$GLOBALS['gMessage']->setForwardUrl($GLOBALS['gNavigation']->getPreviousUrl());
		$GLOBALS['gNavigation']->deleteLastUrl();
		$e->showHtml();
	}
	
	$GLOBALS['gDb']->endTransaction();
	
	// wenn Daten des eingeloggten Users geaendert werden, dann Session-Variablen aktualisieren
	if((int) $user->getValue('usr_id') === (int) $gCurrentUser->getValue('usr_id')) {
		$gCurrentUser = $user;
	}
	
	unset($_SESSION['profile_request']);
	$GLOBALS['gNavigation']->deleteLastUrl();
	
}

/**
 * build the page
 */
function build_page () {
	
	# set headline of the script
	$headline = $GLOBALS['gL10n']->get('L4P_REGISTRATION_RECEIVED');
	
	$GLOBALS['gNavigation']->addUrl(CURRENT_URL, $headline);
	
	// create html page object
	$page = new HtmlPage($headline);
	
	$page->hideMenu();
	
	$html =<<<EOD
<p>Thank you for registering. We will contact you shortly</p>
EOD;
	
	$page->addHtml( $html );
	
	$page->show();
}

###
build_page();


?>