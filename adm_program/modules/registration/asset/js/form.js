'use strict';

/**
 * reg form wire up
 */
const regFormFields = window.regFormFields;

function hideRoleBasedFields() {
    $(`.school`).removeAttr('required').closest('.form-group').hide().removeClass('admidio-form-group-required');
    $(`.matriculation_year`).removeAttr('required').closest('.form-group').hide().removeClass('admidio-form-group-required');

    $(`[name="reference_1"]`).removeAttr('required').closest('.form-group').hide().removeClass('admidio-form-group-required');
    $(`[name="reference_2"]`).removeAttr('required').closest('.form-group').hide().removeClass('admidio-form-group-required');
}
function toggleApplicationFields(event) {
    const applicationType = event.target.value;
    hideRoleBasedFields();
    console.log(applicationType)
    if(applicationType == 'member') {
        $(`.school`).attr('required', 'required').closest('.form-group').show().addClass('admidio-form-group-required');
        $(`.matriculation_year`).attr('required', 'required').closest('.form-group').show().addClass('admidio-form-group-required');
    } else if(applicationType == 'associate') {
        $(`[name="reference_1"]`).attr('required', 'required').closest('.form-group').show().addClass('admidio-form-group-required');
        $(`[name="reference_2"]`).attr('required', 'required').closest('.form-group').show().addClass('admidio-form-group-required');
    }
}
/** Validating inputs for registration form: Ends here */

/** #
 * @author: Akshay
 * @since: 5 June 2018
 */
$(document).ready(() => {
    $('#application_fields').after($('#gb_category_SCHOOL_INFORMATION'));
    $('input.first_name').attr('maxlength', '100');
    $('input.last_name').attr('maxlength', '100');
    $('input.email').attr('maxlength', '100');
    $('input.matriculation_year').attr('type', 'text').attr('minlength', 4).attr('size', 4).attr('maxlength', 4);
    $(`[name="reference_1"]`).attr('maxlength', '100');
    $(`[name="reference_2"]`).attr('maxlength', '100');
    hideRoleBasedFields();
    document.getElementById('application_type').addEventListener('change', function(event){
        toggleApplicationFields(event)
    });
});

/**
 * Submit registration form throught the ajax and redirects to other page on blogger
 */
document.getElementById('component_membership_form').addEventListener('submit', function (event) {
    event.preventDefault();
    event.stopPropagation();
    const $form = $(event.target);
    const formData = $form.serializeArray();
    $.ajax({
        url: $form.attr('action'),
        method: 'post',
        data: formData,
        beforeSend(){
            $('#btn_save').attr('disabled', true).html('Submitting.');
        },
        success(response) {
            if(response.statusCode == 'res_01'){
                top.location.href = 'http://www.cantabnyc.org/p/almost-there.html?email='+response.data;
            }else {
                alert('Something went wrong :(. please try again ');
                throw new Error(`Can't register now. ${response}`);
            }
        },
        error(response){
            alert('Something went wrong :(. please try again ');
            throw new Error(`Can't register now. ${response}`);
        }
    });
    return false;
})
