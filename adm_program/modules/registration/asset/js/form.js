'use strict';

/**
 * reg form wire up
 */
var regFormFields = window.regFormFields;

(function () {

	/**
  * keep a global reference around
  */
	var thingy = {};

	/**
  * initialisation
  */
	thingy.init = function () {

		var self = this;

		this.dom = {};
		this.dom.elements = {};
		this.dom.form = jQuery('#component_membership_form');

		for (var k in window.l4p.config.form) {
			var name = window.l4p.config.form[k];

			this.dom.elements[name] = jQuery('input[name="' + k + '"]', this.dom.form);

			if (this.dom.elements[name].length == 0) {
				this.dom.elements[name] = jQuery('select[name="' + k + '"]', this.dom.form);
			}

			if (this.dom.elements[name].length == 0) {
				this.dom.elements[name] = jQuery('textarea[name="' + k + '"]', this.dom.form);
			}
        }

        // event handlers
        this.dom.elements['Membership Type'].on('change', null, { self: self }, function (evnt) {
            evnt.data.self.onchange_membership(evnt);
        });
        // startup
        if($('.form-control--matriculation_year').length) {
            $('.form-control--matriculation_year').attr('type', 'number')
                                                .attr('min', 1900)
                                                .attr('maxlength', 4)
                                                .attr('max', (new Date).getFullYear());
        }

        // thingy.onchange_membership();
	};

	/**
  * event handler
  */
	thingy.onchange_membership = function (evnt) {
		var self = this;
		var val = $(evnt.target).val();
		var picked = $('#' + evnt.target.id + ' option:selected').text().trim().toLowerCase();

		hideRoleBasedFields();
		var fields = void 0;
		if (picked == 'associate') {
			// show references fields for associated members
			fields = ['professional_information'];
		} else if (picked == 'member') {
			fields = ['school_information'];
		}
		showFields(fields);
	};

	/**
  * keep a global reference around
  */
	window.l4p = window.l4p || { config: {}, modules: {} };
	window.l4p.modules.form = thingy;

	// initialise
	window.setTimeout(function () {
		thingy.init();
	}, 2000);
})();

function hideRoleBasedFields() {
	var typeBasedFields = ['school_information', 'professional_information'];
	typeBasedFields.forEach(function (item) {
		regFormFields.form[item].forEach(function (i) {
			$('.form-control--' + i.name.toLowerCase()).removeAttr('required').closest('.form-group').hide().removeClass('admidio-form-group-required');
		});
	});
}

function showFields(fields) {
	fields.forEach(function (i) {
		regFormFields.form[i].forEach(function (xField) {
			$('.form-control--' + xField.name.toLowerCase()).prop('required', true).closest('.form-group').show().addClass('admidio-form-group-required');
		});
	});
}
/** Validating inputs for registration form: Ends here */

/** #
 * @author: Akshay
 * @since: 5 June 2018
 */
$(document).ready(function () {
	hideRoleBasedFields();
});
