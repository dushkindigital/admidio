<?php
/**
 ***********************************************************************************************
 * Create and edit categories
 *
 * @copyright 2004-2018 The Admidio Team
 * @see http://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

/******************************************************************************
 * Parameters:
 *
 * men_id: Id of the menu that should be edited
 *
 ****************************************************************************/

require_once(__DIR__ . '/../../system/common.php');
require(__DIR__ . '/../../system/login_valid.php');

// Initialize and check the parameters
$getMenId = admFuncVariableIsValid($_GET, 'men_id', 'int');


// Rechte pruefen
if(!$gCurrentUser->isAdministrator())
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

/**
 * @param array<int,string> $menuList
 * @param int               $level
 * @param int               $menId
 * @param int               $parentId
 */
function subMenu(&$menuList, $level, $menId, $parentId = null)
{
    global $gDb;

    $sqlConditionParentId = '';
    $queryParams = array($menId);

    // Erfassen des auszugebenden Menu
    if ($parentId > 0)
    {
        $sqlConditionParentId .= ' AND men_men_id_parent = '.$parentId;
        $queryParams[] = $parentId;
    }
    else
    {
        $sqlConditionParentId .= ' AND men_men_id_parent IS NULL';
    }
    $sql = 'SELECT *
              FROM '.TBL_MENU.'
             WHERE men_node = 1
               AND men_id  <> '.$menId.'
                   '.$sqlConditionParentId;
    $childStatement = $gDb->query($sql);

    $parentMenu = new TableMenu($gDb);
    $einschub = str_repeat('&nbsp;', $level * 3) . '&#151;&nbsp;';

    while($menuEntry = $childStatement->fetch())
    {
        $parentMenu->clear();
        $parentMenu->setArray($menuEntry);

        // add entry to array of all menus
        $menuList[(int) $parentMenu->getValue('men_id')] = $einschub . $parentMenu->getValue('men_name');

        subMenu($menuList, ++$level, $menId, (int) $parentMenu->getValue('men_id'));
    }
}

// set module headline
if($getMenId > 0)
{
    $headline = $gL10n->get('SYS_EDIT_VAR', ($gL10n->get('SYS_MENU')));
}
else
{
    $headline = $gL10n->get('SYS_CREATE_VAR', ($gL10n->get('SYS_MENU')));
}

// create menu object
$menu = new TableMenu($gDb);

// systemcategories should not be renamed
$roleViewSet[] = 0;

if($getMenId > 0)
{
    $menu->readDataById($getMenId);

    // Read current roles rights of the menu
    $display = new RolesRights($gDb, 'menu_view', $getMenId);
    $roleViewSet = $display->getRolesIds();
}

if(isset($_SESSION['menu_request']))
{
    // due to incorrect input, the user has returned to this form
    // Now write the previously entered content into the object
    $menu->setArray($_SESSION['menu_request']);
    unset($_SESSION['menu_request']);
}

$gNavigation->addUrl(CURRENT_URL, $headline);

// create html page object
$page = new HtmlPage($headline);

// add back link to module menu
$menuCreateMenu = $page->getMenu();
$menuCreateMenu->addItem('menu_item_back', $gNavigation->getPreviousUrl(), $gL10n->get('SYS_BACK'), 'back');

// alle aus der DB aus lesen
$sqlRoles = 'SELECT rol_id, rol_name, org_shortname, cat_name
               FROM '.TBL_ROLES.'
         INNER JOIN '.TBL_CATEGORIES.'
                 ON cat_id = rol_cat_id
         INNER JOIN '.TBL_ORGANIZATIONS.'
                 ON org_id = cat_org_id
              WHERE rol_valid  = 1
                AND rol_system = 0
                AND cat_name_intern <> \'EVENTS\'
           ORDER BY cat_name, rol_name';
$rolesViewStatement = $gDb->query($sqlRoles);
// var_dump($rolesViewStatement);

$parentRoleViewSet = array();
while($rowViewRoles = $rolesViewStatement->fetch())
{
    // Jede Rolle wird nun dem Array hinzugefuegt
    $parentRoleViewSet[] = array(
        $rowViewRoles['rol_id'],
        $rowViewRoles['rol_name'] . ' (' . $rowViewRoles['org_shortname'] . ')',
        $rowViewRoles['cat_name']
    );
}
// show form
$form = new HtmlForm('menu_edit_form', safeUrl(ADMIDIO_URL . FOLDER_MODULES . '/menu/menu_function.php', array('men_id' => $getMenId, 'mode' => 1)), $page);

$fieldRequired = HtmlForm::FIELD_REQUIRED;
$fieldDefault  = HtmlForm::FIELD_DEFAULT;

if((bool) $menu->getValue('men_standard'))
{
    $fieldRequired = HtmlForm::FIELD_DISABLED;
    $fieldDefault  = HtmlForm::FIELD_DISABLED;
}

$menuList = array();
subMenu($menuList, 1, (int) $menu->getValue('men_id'));

$form->addInput(
    'men_name', $gL10n->get('SYS_NAME'), $menu->getValue('men_name', 'database'),
    array('maxLength' => 100, 'property'=> HtmlForm::FIELD_REQUIRED, 'helpTextIdLabel' => 'MEN_NAME_DESC')
);

if($getMenId > 0)
{
    $form->addInput(
        'men_name_intern', $gL10n->get('SYS_INTERNAL_NAME'), $menu->getValue('men_name_intern', 'database'),
        array('maxLength' => 100, 'property' => HtmlForm::FIELD_DISABLED, 'helpTextIdLabel' => 'SYS_INTERNAL_NAME_DESC')
    );
}

$form->addMultilineTextInput(
    'men_description', $gL10n->get('SYS_DESCRIPTION'), $menu->getValue('men_description', 'database'), 2,
    array('maxLength' => 4000)
);

$form->addSelectBox(
    'men_men_id_parent', $gL10n->get('MEN_MENU_LEVEL'), $menuList,
    array(
        'property'        => HtmlForm::FIELD_REQUIRED,
        'defaultValue'    => (int) $menu->getValue('men_men_id_parent'),
        'helpTextIdLabel' => 'MEN_MENU_LEVEL_DESC'
    )
);


$sql = 'SELECT com_id, com_name
          FROM '.TBL_COMPONENTS.'
      ORDER BY com_name';
$form->addSelectBoxFromSql(
    'men_com_id', $gL10n->get('MEN_MODULE_RIGHTS'), $gDb, $sql,
    array(
        'property'        => $fieldDefault,
        'defaultValue'    => (int) $menu->getValue('men_com_id'),
        'helpTextIdLabel' => 'MEN_MODULE_RIGHTS_DESC'
    )
);

$form->addSelectBox(
    'menu_view', $gL10n->get('SYS_VISIBLE_FOR'), $parentRoleViewSet,
    array('defaultValue' => $roleViewSet, 'multiselect' => true)
);
// die(var_dump($parentRoleViewSet));

if((bool) $menu->getValue('men_node') === false)
{
    $form->addInput(
        'men_url', $gL10n->get('ORG_URL'), $menu->getValue('men_url', 'database'),
        array('maxLength' => 100, 'property' => $fieldRequired)
    );
}

$form->addInput(
    'men_icon', $gL10n->get('SYS_ICON'), $menu->getValue('men_icon', 'database'),
    array(
        'maxLength' => 100,
        'helpTextIdLabel' => $gL10n->get('SYS_FONT_AWESOME_DESC', array('<a href="https://fontawesome.com/icons?d=gallery&s=brands,solid&m=free" target="_blank">', '</a>')),
        'class' => 'form-control-small'
    )
);
// die(var_dump($menuList));
$html = $form->addSubmitButton(
    'btn_save', $gL10n->get('SYS_SAVE'),
    array('icon' => 'fa-check')
);
$form->show();
// die($g_root_path);
// add form to html page and show page
$addMenuForm = <<<HTML
<div class="admidio-form-required-notice"><span>Required fields</span></div>
<form action="{$g_root_path}/adm_program/modules/menu/menu_function.php?men_id=0&mode=1"
    id="menu_edit_form" method="post" role="form" class=" form-horizontal form-dialog">
    <div id="men_name_group" class="form-group admidio-form-group-required"><label for="men_name" class="col-sm-3 control-label">Name<img class="admidio-icon-help" src="{$g_root_path}/adm_themes/modern/icons/help.png"
                title="Note" alt="Help" data-toggle="popover" data-html="true" data-trigger="hover" data-placement="auto" data-content="#MEN_NAME_DESC#"
            /></label>
        <div class="col-sm-9"><input type="text" name="men_name" id="men_name" value="" class="form-control" minlength=""
                maxlength="100" required /></div>
    </div>
    <div id="men_description_group" class="form-group"><label for="men_description" class="col-sm-3 control-label">Description</label>
        <div class="col-sm-9"><textarea name="men_description" rows="2" cols="80" id="men_description" class="form-control"
                maxlength="4000"></textarea>
            <small class="characters-count">(still <span id="men_description_counter" class="">255</span> characters)</small></div>
    </div>
    <div id="men_men_id_parent_group" class="form-group admidio-form-group-required"><label for="men_men_id_parent" class="col-sm-3 control-label">Menu Level<img class="admidio-icon-help" src="{$g_root_path}/adm_themes/modern/icons/help.png"
                title="Note" alt="Help" data-toggle="popover" data-html="true" data-trigger="hover" data-placement="auto" data-content="#MEN_MENU_LEVEL_DESC#"
            /></label>
        <div class="col-sm-9"><select name="men_men_id_parent" id="men_men_id_parent" class="form-control" ><option
                    value="">- Select option -</option></select></div>
    </div>
    <div id="men_com_id_group" class="form-group"><label for="men_com_id" class="col-sm-3 control-label">Module Rights<img class="admidio-icon-help" src="{$g_root_path}/adm_themes/modern/icons/help.png"
                title="Note" alt="Help" data-toggle="popover" data-html="true" data-trigger="hover" data-placement="auto" data-content="#MEN_MODULE_RIGHTS_DESC#"
            /></label>
        <div class="col-sm-9"><select name="men_com_id" id="men_com_id" class="form-control"><option value=""> </option>
                <option
                    value="1">Admidio Core</option>
            </select>
        </div>
    </div>
    <div id="menu_view_group" class="form-group"><label for="menu_view" class="col-sm-3 control-label">Visible For</label>
        <div class="col-sm-9"><select name="menu_view[]" id="menu_view" class="form-control" multiple><option value=""> </option>
        </select></div>
    </div>
    <div id="men_url_group" class="form-group admidio-form-group-required"><label for="men_url" class="col-sm-3 control-label">URL</label>
        <div class="col-sm-9"><input type="text" name="men_url" id="men_url" value="" class="form-control" minlength="" maxlength="100"
                required /></div>
    </div>
    <div id="men_icon_group" class="form-group"><label for="men_icon" class="col-sm-3 control-label">Icon<img class="admidio-icon-help" src="http://localhost/akshay/SO_FRVP_A1348/cantab/adm_themes/modern/icons/help.png"
                title="Note" alt="Help" data-toggle="popover" data-html="true" data-trigger="hover" data-placement="auto" data-content="##SYS_FONT_AWESOME_DESC##"
            /></label>
        <div class="col-sm-9"><input type="text" name="men_icon" id="men_icon" value="" class="form-control form-control-small"
                minlength="" maxlength="100" /></div>
    </div><button class="btn btn-default  btn-primary" id="btn_save" name="btn_save" type="submit"><img src="fa-check" alt="Save"
        />Save</button><div class="form-alert" style="display: none;">&nbsp;</div>
</form>

HTML;

// $page->addHtml($addMenuForm);
// $page->addHtml($form->show());

$page->show();

