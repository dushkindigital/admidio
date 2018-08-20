<?php
/**
 ***********************************************************************************************
 * Overview and maintenance of all menus
 *
 * @copyright 2004-2018 The Admidio Team
 * @see http://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */
require_once(__DIR__ . '/../../system/common.php');

// Rechte pruefen
if(!$gCurrentUser->isAdministrator())
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

$headline = $gL10n->get('SYS_MENU');

// create html page object
$page = new HtmlPage($headline);
$page->enableModal();

$page->addJavascript('
    function moveMenu(direction, menID) {
        var actRow = document.getElementById("row_men_" + menID);
        var childs = actRow.parentNode.childNodes;
        var prevNode    = null;
        var nextNode    = null;
        var actRowCount = 0;
        var actSequence = 0;
        var secondSequence = 0;

        // erst einmal aktuelle Sequenz und vorherigen/naechsten Knoten ermitteln
        for (i = 0; i < childs.length; i++) {
            if (childs[i].tagName === "TR") {
                actRowCount++;
                if (actSequence > 0 && nextNode === null) {
                    nextNode = childs[i];
                }

                if (childs[i].id === "row_men_" + menID) {
                    actSequence = actRowCount;
                }

                if (actSequence === 0) {
                    prevNode = childs[i];
                }
            }
        }

        // entsprechende Werte zum Hoch- bzw. Runterverschieben ermitteln
        if (direction === "UP") {
            if (prevNode !== null) {
                actRow.parentNode.insertBefore(actRow, prevNode);
                secondSequence = actSequence - 1;
            }
        } else {
            if (nextNode !== null) {
                actRow.parentNode.insertBefore(nextNode, actRow);
                secondSequence = actSequence + 1;
            }
        }

        if (secondSequence > 0) {
            // Nun erst mal die neue Position von der gewaehlten Kategorie aktualisieren
            $.get("' . safeUrl(ADMIDIO_URL . FOLDER_MODULES . '/menu/menu_function.php', array('mode' => 3)) . '&men_id=" + menID + "&sequence=" + direction);
        }
    }');

// get module menu
$menuMenu = $page->getMenu();

$gNavigation->addStartUrl(CURRENT_URL, $headline);

// define link to create new menu
$menuMenu->addItem(
    'admMenuItemNew', ADMIDIO_URL . FOLDER_MODULES . '/menu/menu_new.php',
    $gL10n->get('SYS_CREATE_ENTRY'), 'add.png'
);

// Create table object
$menuOverview = new HtmlTable('tbl_menues', $page, true);

// create array with all column heading values
$columnHeading = array(
    $gL10n->get('SYS_TITLE'),
    '&nbsp;',
    $gL10n->get('ORG_URL'),
    '<i class="fas fa-star" data-toggle="tooltip" title="' . $gL10n->get('CAT_DEFAULT_VAR', array($gL10n->get('MEN_MENU_ITEM'))) . '"></i>',
    '&nbsp;'
);
$menuOverview->setColumnAlignByArray(array('left', 'left', 'left', 'center', 'right'));
$menuOverview->addRowHeadingByArray($columnHeading);

$sql = 'SELECT men_id, men_name
          FROM '.TBL_MENU.'
         WHERE men_men_id_parent IS NULL
      ORDER BY men_order';
$mainMenStatement = $gDb->query($sql);
$collection = $mainMenStatement->fetchAll();
foreach ($collection as $key => $item) {
    $sql = 'SELECT men_id, men_men_id_parent, men_name, men_description, men_standard, men_url
                FROM '.TBL_MENU.'
            WHERE men_men_id_parent = '.$item['men_id'].'
            ORDER BY men_men_id_parent DESC, men_order';
    $menuStatement = $gDb->query($sql);
    $collectionOfMenus = $menuStatement->fetchAll();
    $menuGroup = 0;
    // add row to table: Starts here
    foreach ($collectionOfMenus as $menuKey => $menuItem) {
        $menIdParent = (int) $menuItem['men_men_id_parent'];
        if($menuGroup !== $menIdParent){
            $blockId = 'admMenu_'.$menIdParent;
            $menuOverview->addTableBody();
            $menuOverview->addRow('', array('class' => 'admidio-group-heading'));
            $menuOverview->addColumn('<span id="caret_'.$blockId.'" class="caret"></span>'.$gL10n->get($item['men_name']),
                              array('id' => 'group_'.$blockId, 'colspan' => '8'));
            $menuOverview->addTableBody('id', $blockId);
            $menuGroup = $menIdParent;
        }
        $menuOverview->addRowByArray([
<<<HTML
<a href="{$g_root_path}/adm_program/modules/menu/menu_new.php?men_id={$menuItem['men_id']}">
    {$gL10n->get($menuItem['men_name'])}
</a>
HTML
,
<<<HTML
<div style="text-align: left;">
    <a class="admidio-icon-link" href="javascript:moveMenu('UP', {$menuItem['men_id']})">
        <img src="{$g_root_path}/adm_themes/modern/icons/arrow_up.png" alt="Move up Menu" title="" data-original-title="Move up Menu">
    </a>
    <a class="admidio-icon-link" href="javascript:moveMenu('DOWN', {$menuItem['men_id']})">
        <img src="{$g_root_path}/adm_themes/modern/icons/arrow_down.png" alt="Move down Menu" title="" data-original-title="Move down Menu"></a>
</div>
HTML
,
<<<HTML
<a href="{$g_root_path}{$menuItem['men_url']}">
    {$menuItem['men_url']}
</a>
HTML
,
<<<HTML
<img class="admidio-icon-info" src="{$g_root_path}/adm_themes/modern/icons/star.png" alt="Standard Menu item" title="" data-original-title="Standard Menu item">
HTML
,
<<<HTML
<a class="admidio-icon-link" href="{$g_root_path}/adm_program/modules/menu/menu_new.php?men_id=4"><img src="{$g_root_path}/adm_themes/modern/icons/edit.png" alt="Edit" title="" data-original-title="Edit"></a>
HTML
,
        ],
            // row attributes
            'row_men_'.$menuItem['men_id']
        );
    }
    // add row to table: Ends here

}
$page->addHtml($menuOverview->show());
$page->show();
