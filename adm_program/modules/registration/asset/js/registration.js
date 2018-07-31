'use strict';
let userId;
let token;
let parentWindow = window.parent;
let parentLocation = window.parent.location;
let $rootElem;

$(document).ready(function () {
    // # on document ready: Starts here
    $rootElem = $('#root');
    $('#application_fields').after($('#gb_category_SCHOOL_INFORMATION'));
    getParams();
    getUserDetails();
    // # on document ready: Ends here
});

function getParams() {
    try {
var url = (window.location != window.parent.location)
           ? document.referrer
           : document.location.href;
        const urlObj = new URL(url);
        const params = urlObj.search.replace('?', '').split('&');
        const paramsArr = [];
        params.forEach((item, index) => {
            let keyValPair = item.split('=');
            paramsArr[keyValPair[0]] = keyValPair[1];
        });
        userId = paramsArr['uid'];
        token = paramsArr['token'];
    } catch (error) {
        console.log(error.message)
    }
}
function getUserDetails() {
    $.ajax({
        url: `${window.gRootPath}/adm_program/modules/registration/validate-activation.php`,
        method: 'POST',
        // dataType: 'JSON',
        data: {
            uid: userId,
            token,
            _action: 'get_user'
        },
        success(success){
            if(success.status == 'SUCCESS'){
                $rootElem.html(success.data)
            }else {
                $rootElem.html(`
                <div class="alert alert-warning">
                    ${success.msg}
                </div>
                `)
            }
        },
        error(error){
            $rootElem.html(`
            <div class="alert alert-warning">
                ${error.msg}
            </div>
            `)
        },
    });
}
function setNewPassword(event) {
    event.preventDefault();
    event.stopPropagation();

    const password = $('#new_password').val();
    $.ajax({
        url: `${window.gRootPath}/adm_program/modules/registration/validate-activation.php`,
        method: 'POST',
        // dataType: 'JSON',
        data: {
            uid: userId,
            password,
            _action: 'validate_activation_code'
        },
        success(success){
            $rootElem.html(success.msg)
            // console.info(success)
        },
        error(error){
            console.error(error)
        },
    });
    return false;
}
function validateConfirmedPassword(event){
    const password = $('#new_password').val();
    const confirm_password = $('#confirm_password').val();

    const responseElem = $('#'+event.target.id + ' + .form-ctrl__response');
    responseElem.hide();
    if(confirm_password != password){
        responseElem.show().html('Confirmed password does not match.');
    }
}