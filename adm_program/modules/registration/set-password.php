<?php
require_once __DIR__ . '/../../../adm_program/system/common.php';

require_once __DIR__ . '/engine/bootstrap.php';
// header-block
$page = new HtmlPage('');
$page->addHtml(<<<HTML
<style>
#header-block, #copyright,
#right-block {
    display:none;
}
body {
    background: none;
}
#root {
    /* margin: 0 -15px; */
}
</style>
HTML
// keep this extra comment
);

$page->hideMenu();

$htmlContent = <<<HTML
<div id="root"></div>
HTML;

$page->addHtml($htmlContent);
$page->addHtml('<script src="' . ADMIDIO_URL . '/adm_program/libs/zxcvbn/dist/zxcvbn.js"></script>');
$page->addHtml('<script src="' . ADMIDIO_URL . '/adm_program/modules/registration/asset/js/registration.js"></script>');
$passwordStrenthScript = <<<HTML
<script>
</script>
HTML;

$page->addHtml($passwordStrenthScript);

$page->show();
