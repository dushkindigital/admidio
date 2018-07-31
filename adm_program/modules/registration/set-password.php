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
$page->addHtml('<script src="' . ADMIDIO_URL . '/adm_program/modules/registration/asset/js/registration.js"></script>');
$page->addHtml('<script src="' . ADMIDIO_URL . '/adm_program/libs/zxcvbn/dist/zxcvbn.js"></script>');
$passwordStrenthScript = <<<HTML
<script>
$(document).ready(function(){
    $("#admidio-password-strength-minimum").css("margin-left", "calc(" + $("#admidio-password-strength").css("width") + " / 4 * 1)");
})
// Updating the progress bar on keyp event: Starts here
document.querySelector("#new_password").addEventListener('keyup', function(e) {
    var result = zxcvbn(e.target.value, []);
    var cssClasses = ["progress-bar-danger", "progress-bar-danger", "progress-bar-warning", "progress-bar-info", "progress-bar-success"];
    var progressBar = $("#admidio-password-strength .progress-bar");
    progressBar.attr("aria-valuenow", result.score * 25);
    progressBar.css("width", result.score * 25 + "%");
    progressBar.removeClass(cssClasses.join(" "));
    progressBar.addClass(cssClasses[result.score]);
});
// Updating the progress bar on keyp event: Ends here
</script>
HTML;

$page->addHtml($passwordStrenthScript);

$page->show();
