<?php
require_once __DIR__ . '/../adm_program/system/common.php';

require_once __DIR__ . '/engine/bootstrap.php';

// Initialize and check the parameters
$getUserId = admFuncVariableIsValid($_GET, 'user_id', 'int');
$getNewUser = admFuncVariableIsValid($_GET, 'new_user', 'int');
$getLastname = stripslashes(admFuncVariableIsValid($_GET, 'lastname', 'string'));
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
function build_form($form, $datum_user)
{

    $export_field_names = array(
        'usr_login_name' => 'usr_login_name', #,
        #'usr_password'     => 'usr_password',
        #'password_confirm' => 'password_confirm'
    );

    // *******************************************************************************
    // Loop over all categories and profile fields except the category 'master data'
    // *******************************************************************************

    $category = '';
    /**
     * @author: Akshay
     * @since: 5 june 2018
     * @todo: Add custom fields for regsistration of members dynamically
     * Loop through the fields: Starts here
     */
    $formFields = $GLOBALS['gProfileFields']->mProfileFields;
	$reg_form_fields = [];
    foreach ($formFields as $field) {

        $showField = false;

        // bei schneller Registrierung duerfen nur die Pflichtfelder ausgegeben werden
        // E-Mail ist Ausnahme und muss immer angezeigt werden
        if (
            ($GLOBALS['gPreferences']['registration_mode'] == 1) &&
            ($field->getValue('usf_mandatory') == 1 ||
            $field->getValue('usf_name_intern') === 'EMAIL')
        ) {
            $showField = true;
        } elseif ($GLOBALS['gPreferences']['registration_mode'] == 2) {
            // bei der vollstaendigen Registrierung alle Felder anzeigen
            $showField = true;

        } elseif ($GLOBALS['gPreferences']['registration_mode'] == \cantabnyc\get_configs()->preference->registration_mode) {
            # temp registration
            $l4p_fields = \cantabnyc\get_configs()->form_fields->reg;
            if (\in_array($field->getValue('usf_name_intern'), $l4p_fields)) {
                $showField = true;
            }
        }

        // Kategorienwechsel den Kategorienheader anzeigen
        // bei schneller Registrierung duerfen nur die Pflichtfelder ausgegeben werden
        if ($category !== $field->getValue('cat_name') && $showField) {
            if ($category !== '') {
                // div-Container admGroupBoxBody und admGroupBox schliessen
                $form->closeGroupBox();
            }
            $category = $field->getValue('cat_name');
			$cat_name_intern = strtolower($field->getValue('cat_name_intern'));
            //$form->addHtml('<a id="cat-' . $field->getValue('cat_id') . '"></a>');
            $form->openGroupBox('gb_category_' . $field->getValue('cat_name_intern'), NULL, 'form__fieldset form__fieldset--'.$cat_name_intern);

        }

        // bei schneller Registrierung duerfen nur die Pflichtfelder ausgegeben werden

        if ($showField) {

            // add profile fields to form
            $fieldProperty = FIELD_DEFAULT;
            $helpId = '';
			$htmlAfterFormCtrl = '';
            $usfNameIntern = $field->getValue('usf_name_intern');

            # keep track of names to ids
            $export_field_names['usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id')] = $field->getValue('usf_name');

			//$reg_form_fields[$cat_name_intern][] = [];
			$reg_form_fields[$cat_name_intern][] = [
				'label' => $field->getValue('usf_name'),
				'name' => $field->getValue('usf_name_intern'),
				'id' => 'usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
			];

			//'usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id')

            if ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_mandatory') == 1) {
                // set mandatory field
                $fieldProperty = FIELD_REQUIRED;
            } elseif (
                ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_hidden') == 1)
            ) {
                // set mandatory field
                $fieldProperty = FIELD_HIDDEN;
            }

            if (strlen($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_description')) > 0) {
                $helpId = array('user_field_description', $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name_intern'));
            }

			if(
				!is_null($field->getValue('usf_description')) ||
				!empty($field->getValue('usf_description'))
			){
				$htmlAfterFormCtrl = '<div class="form-control__help">'.$field->getValue('usf_description').'</div>';
			}
            // code for different field types
            if ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'CHECKBOX') {
                $form->addCheckbox(
                    'usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
                    $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
                    (bool) $datum_user->getValue($usfNameIntern),
                    array(
                        'property' => $fieldProperty,
						'class' => 'form-control--'.strtolower($field->getValue('usf_name_intern')),
                        'helpTextIdLabel' => $helpId,
						'htmlAfter' => $htmlAfterFormCtrl,
                        'icon' => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database'),
                    )
                );
            } elseif (
                $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'DROPDOWN' ||
                $usfNameIntern === 'COUNTRY'
            ) {

                // set array with values and set default value
                if ($usfNameIntern === 'COUNTRY') {
                    $arrListValues = $GLOBALS['gL10n']->getCountries();
                    $defaultValue = null;
                    if (
                        (int) $datum_user->getValue('usr_id') === 0 &&
                        strlen($GLOBALS['gPreferences']['default_country']) > 0
                    ) {
                        $defaultValue = $GLOBALS['gPreferences']['default_country'];
                    } elseif (
                        $datum_user->getValue('usr_id') > 0 &&
                        strlen($datum_user->getValue($usfNameIntern)) > 0
                    ) {
                        $defaultValue = $datum_user->getValue($usfNameIntern, 'database');
                    }
                } else {
                    $arrListValues = $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_value_list');
                    // $arrListValues = explode(',', $arrListValues);
                    // var_dump($arrListValues);
                    $defaultValue = $datum_user->getValue($usfNameIntern, 'database');
                }

                $form->addSelectBox(
                    'usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
                    $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
                    $arrListValues,
                    array(
                        'property' => $fieldProperty,
                        'defaultValue' => $defaultValue,
						'class' => 'form-control--'.strtolower($field->getValue('usf_name_intern')),
                        'helpTextIdLabel' => $helpId,
						'htmlAfter' => $htmlAfterFormCtrl,
                        'icon' => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database'),
                    )
                );
            } elseif ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'RADIO_BUTTON') {
                $showDummyRadioButton = false;
                if ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_mandatory') == 0) {
                    $showDummyRadioButton = true;
                }

                $form->addRadioButton(
                    'usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
                    $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
                    $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_value_list'),
                    array(
                        'property' => $fieldProperty,
                        'defaultValue' => $datum_user->getValue($usfNameIntern, 'database'),
						'class' => 'form-control--'.strtolower($field->getValue('usf_name_intern')),
                        'showNoValueButton' => $showDummyRadioButton,
                        'helpTextIdLabel' => $helpId,
						'htmlAfter' => $htmlAfterFormCtrl,
                        'icon' => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database'),
                    )
                );
            } elseif ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'TEXT_BIG') {
                $form->addMultilineTextInput(
                    'usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
                    $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
                    $datum_user->getValue($usfNameIntern),
                    3,
                    array(
                        'maxLength' => 4000,
                        'property' => $fieldProperty,
						'class' => 'form-control--'.strtolower($field->getValue('usf_name_intern')),
                        'helpTextIdLabel' => $helpId,
						'htmlAfter' => $htmlAfterFormCtrl,
                        'icon' => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database'),
                    )
                );
            } else {
                $fieldType = 'text';

                if ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'DATE') {
                    if ($usfNameIntern === 'BIRTHDAY') {
                        $fieldType = 'birthday';
                    } else {
                        $fieldType = 'date';
                    }
                    $maxlength = '10';
                } elseif ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'EMAIL') {
                    // email could not be longer than 254 characters
                    $fieldType = 'email';
                    $maxlength = '254';
                } elseif ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'URL') {
                    // maximal browser compatible url length will be 2000 characters
                    $maxlength = '2000';
                } elseif ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_type') === 'NUMBER') {
                    $fieldType = 'number';
                    $maxlength = array(0, 9999999999, 1);
                } elseif ($GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'cat_name_intern') === 'SOCIAL_NETWORKS') {
                    $maxlength = '255';
                } else {
                    $maxlength = '50';
                }

                $form->addInput(
                    'usf-' . $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_id'),
                    $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_name'),
                    $datum_user->getValue($usfNameIntern),
                    array(
                        'type' => $fieldType,
                        'maxLength' => $maxlength,
						'class' => 'form-control--'.strtolower($field->getValue('usf_name_intern')),
                        'property' => $fieldProperty,
                        'helpTextIdLabel' => $helpId,
						'htmlAfter' => $htmlAfterFormCtrl,
                        'icon' => $GLOBALS['gProfileFields']->getProperty($usfNameIntern, 'usf_icon', 'database'),
                    )
                );
            }
        }
    }

    /** Loop through the fields: Ends here */

    // div-Container admGroupBoxBody und admGroupBox schliessen
    $form->closeGroupBox();

    # captcha
    if ($GLOBALS['gPreferences']['enable_registration_captcha'] == 1) {

        $form->openGroupBox('gb_confirmation_of_input', $GLOBALS['gL10n']->get('SYS_CONFIRMATION_OF_INPUT'));
        $form->addCaptcha('captcha_code');
        $form->closeGroupBox();
    }

    # submit button
    $form->addSubmitButton('btn_save', $GLOBALS['gL10n']->get('SYS_SEND'), array('icon' => THEME_URL . '/icons/email.png'));

    return [$export_field_names, $reg_form_fields];
}

/**
 * build the page
 */
function build_page()
{

    # read user data
    $datum_user = new User($GLOBALS['gDb'], $GLOBALS['gProfileFields'], 0);

    # set headline of the script
    $headline = $GLOBALS['gL10n']->get('SYS_REGISTRATION');

    $GLOBALS['gNavigation']->addUrl(CURRENT_URL, $headline);

    // create html page object
    $page = new HtmlPage($headline);
    $page->enableModal();
    $page->addJavascriptFile('adm_program/libs/zxcvbn/dist/zxcvbn.js');

    $page->addHtml('<script src="' . ADMIDIO_URL . '/l4p/asset/js/form.js"></script>');

    $page->addCssFile("l4p/asset/css/component_membership_2.min.css");

    $page->hideMenu();

    /*
    // add back link to module menu
    $profileEditMenu = $page->getMenu();
    $profileEditMenu->addItem('menu_item_back', $GLOBALS['gNavigation']->getPreviousUrl(), $GLOBALS['gL10n']->get('SYS_BACK'), 'back.png');
     */

    // create html form
    $form = new HtmlForm('component_membership_form', ADMIDIO_URL . '/l4p/handle_membership.php', $page);

    $export_field_names = $fields = build_form($form, $datum_user);
	$export_field_names = $export_field_names[0];
	$reg_form_fields = $fields[1];

    $page->addHtml($form->show(false));

    # splice in JS configs
    $page->addJavascript('window.l4p = {config: {form: ' . \json_encode($export_field_names) . '}, modules: {} }');
	$page->addJavascript('window.regFormFields = {form: ' . \json_encode($reg_form_fields) . '}');

    $page->show();
}

###
build_page();