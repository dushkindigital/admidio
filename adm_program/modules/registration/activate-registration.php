<?php
require_once __DIR__ . '/../../../adm_program/system/common.php';

require_once __DIR__ . '/engine/bootstrap.php';

$page = new HtmlPage('Set your password');


$page->hideMenu();
$childPage = ADMIDIO_URL."/adm_program/modules/registration/set-password.php";
$htmlContent = <<<HTML
<iframe src="{$childPage}" style="border: 0; overflow:hidden" width="100%" height=400></iframe>
HTML;

$page->addHtml($htmlContent);
// $page->addHtml('<script type="text/javascript" src="' . ADMIDIO_URL . '/adm_program/modules/registration/asset/js/registration.js"></script>');

$page->show();
