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
