'use strict';
let userId;
let token;
const parentWindow = window.parent;
let $rootElem;

$(document).ready(function () {
    // # on document ready: Starts here
    $rootElem = $('#root');
    getParams();
    getUserDetails();
    // # on document ready: Ends here
});


function getParams() {
    try {
        const urlObj = new URL(parentWindow.location.href);
        const params = urlObj.search.replace('?', '').split('&');
        const paramsArr = [];
        params.forEach((item, index) => {
            let keyValPair = item.split('=');
            paramsArr[keyValPair[0]] = keyValPair[1];
        });
        userId = paramsArr['uid'];
        token = paramsArr['token'];
    } catch (error) {
        throw new Error('ADM_ERROR_1. Error parsing url '. error.getMessage());
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
            $rootElem.html(success.data)
            console.info(success)
        },
        error(error){
            console.error(error)
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
            // $rootElem.html(success.msg)
            console.info(success)
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
