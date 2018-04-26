<?php
require_once (__DIR__ . '/../adm_program/system/common.php');

require_once (__DIR__ . '/engine/bootstrap.php');

// Initialize and check the parameters
$getUserId    = admFuncVariableIsValid($_GET, 'user_id',  'int');
$getNewUser   = admFuncVariableIsValid($_GET, 'new_user', 'int');
$getLastname  = stripslashes(admFuncVariableIsValid($_GET, 'lastname',  'string'));
$getFirstname = stripslashes(admFuncVariableIsValid($_GET, 'firstname', 'string'));

$registrationOrgId = $GLOBALS['gCurrentOrganization']->getValue('org_id');


# has the registration module been disabled in preferences
if ($GLOBALS['gPreferences']['registration_mode'] == 0) {
	$GLOBALS['gMessage']->show($GLOBALS['gL10n']->get('SYS_MODULE_DISABLED'));
	// => EXIT
}

/**
 * build the form
 */
function build_form ($form, $datum_user) {
	
	$export_field_names = array(
		'usr_login_name'   => 'usr_login_name'#,
		#'usr_password'     => 'usr_password',
		#'password_confirm' => 'password_confirm'
	);
	
	// *******************************************************************************
	// Loop over all categories and profile fields except the category 'master data'
	// *******************************************************************************
	
	$category = '';
	
	foreach($GLOBALS['gProfileFields']->mProfileFields as $field) {
		
		$showField = false;
		
			// bei schneller Registrierung duerfen nur die Pflichtfelder ausgegeben werden
			// E-Mail ist Ausnahme und muss immer angezeigt werden
			if (($GLOBALS['gPreferences']['registration_mode'] == 1)
			&& ($field->getValue('usf_mandatory') == 1 || $field->getValue('usf_name_intern') === 'EMAIL'))
			{
					$showField = true;
			}
			elseif ($GLOBALS['gPreferences']['registration_mode'] == 2)
			{
					// bei der vollstaendigen Registrierung alle Felder anzeigen
					$showField = true;
			
			} elseif ($GLOBALS['gPreferences']['registration_mode'] == \cantabnyc\get_configs()->preference->registration_mode) {
				# temp registration
				$l4p_fields = array( 'LAST_NAME', 'FIRST_NAME', 'EMAIL', 'L4P_DB_MEMBERSHIP', 'L4P_DB_SCHOOL', 'L4P_DB_AFFILIATION', 'L4P_DB_MATRICULATION', 'L4P_DB_MESSAGE' );
				
				if (\in_array($field->getValue('usf_name_intern'), $l4p_fields)) {
					$showField = true;
				}
			}
	
			// Kategorienwechsel den Kategorienheader anzeigen
			// bei schneller Registrierung duerfen nur die Pflichtfelder ausgegeben werden
			if($category !== $field->getValue('cat_name') && $showField)
			{
					if($category !== '')
					{
							// div-Container admGroupBoxBody und admGroupBox schliessen
							$form->closeGroupBox();
					}
					$category = $field->getValue('cat_name');
	
					$form->addHtml('<a id="cat-'. $field->getValue('cat_id'). '"></a>');
					$form->openGroupBox('gb_category_'.$field->getValue('cat_name_intern'), $field->getValue('cat_name'));
	
					/*
					if($field->getValue('cat_name_intern') === 'MASTER_DATA')
					{
				
						// add username to form
						$fieldProperty = FIELD_DEFAULT;
						$fieldHelpId   = 'PRO_USERNAME_DESCRIPTION';
						
						$fieldProperty = FIELD_REQUIRED;

						$form->addInput('usr_login_name', $GLOBALS['gL10n']->get('SYS_USERNAME'), $datum_user->getValue('usr_login_name'), array('maxLength' => 35, 'property' => $fieldProperty, 'helpTextIdLabel' => $fieldHelpId, 'class' => 'form-control-small'));
						
						
						// at registration add password and password confirm to form
						$form->addInput(
								'usr_password', $GLOBALS['gL10n']->get('SYS_PASSWORD'), null,
								array('type' => 'password', 'property' => FIELD_REQUIRED, 'minLength' => PASSWORD_MIN_LENGTH, 'passwordStrength' => true, 'helpTextIdLabel' => 'PRO_PASSWORD_DESCRIPTION', 'class' => 'form-control-small')
						);
						$form->addInput('password_confirm', $GLOBALS['gL10n']->get('SYS_CONFIRM_PASSWORD'), null, array('type' => 'password', 'property' => FIELD_REQUIRED, 'minLength' => PASSWORD_MIN_LENGTH, 'class' => 'form-control-small'));
						
						
						$form->addLine();
					}
					*/
			}
	
			// bei schneller Registrierung duerfen nur die Pflichtfelder ausgegeben werden
			if ($showField) {
				
				// add profile fields to form
				$fieldProperty = FIELD_DEFAULT;
				$helpId        = '';
				$usfNameIntern = $field->getValue('usf_name_intern');
				
				# keep track of names to ids
				$export_field_names[ 'usf-'. $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id') ] = $field->getValue('usf_name');
					
					if($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_mandatory') == 1) {
						// set mandatory field
						$fieldProperty = FIELD_REQUIRED;
					}
	
					if(strlen($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_description')) > 0)
					{
							$helpId = array('user_field_description', $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name_intern'));
					}
	
					// code for different field types
					if($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'CHECKBOX')
					{
							$form->addCheckbox(
									'usf-'. $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
									$GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
									(bool) $user->getValue($usfNameIntern),
									array(
											'property'        => $fieldProperty,
											'helpTextIdLabel' => $helpId,
											'icon'            => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database')
									)
							);
					}
					elseif($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'DROPDOWN' || $usfNameIntern === 'COUNTRY')
					{
							// set array with values and set default value
							if($usfNameIntern === 'COUNTRY')
							{
									$arrListValues = $GLOBALS['gL10n']->getCountries();
									$defaultValue  = null;
	
									if((int) $user->getValue('usr_id') === 0 && strlen($GLOBALS['gPreferences']['default_country']) > 0)
									{
											$defaultValue = $GLOBALS['gPreferences']['default_country'];
									}
									elseif($user->getValue('usr_id') > 0 && strlen($user->getValue($usfNameIntern)) > 0)
									{
											$defaultValue = $user->getValue($usfNameIntern, 'database');
									}
							}
							else
							{
									$arrListValues = $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_value_list');
									$defaultValue  = $datum_user->getValue($usfNameIntern, 'database');
							}
	
							$form->addSelectBox(
									'usf-'. $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
									$GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
									$arrListValues,
									array(
											'property'        => $fieldProperty,
											'defaultValue'    => $defaultValue,
											'helpTextIdLabel' => $helpId,
											'icon'            => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database')
									)
							);
					}
					elseif($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'RADIO_BUTTON')
					{
							$showDummyRadioButton = false;
							if($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_mandatory') == 0)
							{
									$showDummyRadioButton = true;
							}
	
							$form->addRadioButton(
									'usf-'.$GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
									$GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
									$GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_value_list'),
									array(
											'property'          => $fieldProperty,
											'defaultValue'      => $user->getValue($usfNameIntern, 'database'),
											'showNoValueButton' => $showDummyRadioButton,
											'helpTextIdLabel'   => $helpId,
											'icon'              => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database')
									)
							);
					}
					elseif($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'TEXT_BIG')
					{
							$form->addMultilineTextInput(
									'usf-'. $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
									$GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
									$datum_user->getValue($usfNameIntern),
									3,
									array(
											'maxLength'       => 4000,
											'property'        => $fieldProperty,
											'helpTextIdLabel' => $helpId,
											'icon'            => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database')
									)
							);
					}
					else
					{
							$fieldType = 'text';
	
							if($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'DATE')
							{
									if($usfNameIntern === 'BIRTHDAY')
									{
											$fieldType = 'birthday';
									}
									else
									{
											$fieldType = 'date';
									}
									$maxlength = '10';
							}
							elseif($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'EMAIL')
							{
									// email could not be longer than 254 characters
									$fieldType = 'email';
									$maxlength = '254';
							}
							elseif($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'URL')
							{
									// maximal browser compatible url length will be 2000 characters
									$maxlength = '2000';
							}
							elseif($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'NUMBER')
							{
									$fieldType = 'number';
									$maxlength = array(0, 9999999999, 1);
							}
							elseif($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'cat_name_intern') === 'SOCIAL_NETWORKS')
							{
									$maxlength = '255';
							}
							else
							{
									$maxlength = '50';
							}
	
							$form->addInput(
								'usf-'. $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
								$GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
								$datum_user->getValue($usfNameIntern),
								array(
									'type' => $fieldType,
									'maxLength' => $maxlength,
									'property' => $fieldProperty,
									'helpTextIdLabel' => $helpId,
									'icon' => $GLOBALS['gProfileFields']->getProperty($usfNameIntern,'usf_icon', 'database')
								)
							);
					}
			}
	}
	
	// div-Container admGroupBoxBody und admGroupBox schliessen
	$form->closeGroupBox();
	
	# captcha
	if ($GLOBALS['gPreferences']['enable_registration_captcha'] == 1) {
		
		$form->openGroupBox('gb_confirmation_of_input', $GLOBALS['gL10n']->get('SYS_CONFIRMATION_OF_INPUT'));
		$form->addCaptcha('captcha_code');
		$form->closeGroupBox();
	}
	
	# submit button
	$form->addSubmitButton('btn_save', $GLOBALS['gL10n']->get('SYS_SEND'), array('icon' => THEME_URL.'/icons/email.png'));
	
	return $export_field_names;
}


/**
 * build the page
 */
function build_page () {
	
	# read user data
	$datum_user = new User($GLOBALS['gDb'], $GLOBALS['gProfileFields'], 0);
	
	# set headline of the script
	$headline = $GLOBALS['gL10n']->get('SYS_REGISTRATION');
	
	$GLOBALS['gNavigation']->addUrl(CURRENT_URL, $headline);
	
	// create html page object
	$page = new HtmlPage($headline);
	$page->enableModal();
	$page->addJavascriptFile('adm_program/libs/zxcvbn/dist/zxcvbn.js');
	
	$page->addHtml('<script type="text/javascript" src="' . ADMIDIO_URL . '/l4p/asset/js/form.js"></script>');
	
	$page->addCssFile( "l4p/asset/css/component_membership_2.min.css" );
	
	$page->hideMenu();
	
	/*
	// add back link to module menu
	$profileEditMenu = $page->getMenu();
	$profileEditMenu->addItem('menu_item_back', $GLOBALS['gNavigation']->getPreviousUrl(), $GLOBALS['gL10n']->get('SYS_BACK'), 'back.png');
	*/
	
	// create html form
	$form = new HtmlForm('component_membership_form', ADMIDIO_URL .'/l4p/handle_membership.php', $page);
	
	$export_field_names = build_form( $form, $datum_user );
	
	$page->addHtml( $form->show(false) );
	
	# splice in JS configs
	$page->addJavascript( 'window.l4p = {config: {form: ' . \json_encode($export_field_names) . '}, modules: {} }' );
	
	$page->show();
}

###
build_page();
