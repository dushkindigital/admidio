/**
 * reg form wire up
 */
const regFormFields = window.regFormFields;

(
	function () {

		/**
		 * keep a global reference around
		 */
		const thingy = {};

		/**
		 * initialisation
		 */
		thingy.init = function () {

			const self = this;

			this.dom = {};
			this.dom.elements = {};
			this.dom.form     = jQuery('#component_membership_form');

			for (let k in window.l4p.config.form) {
				const name = window.l4p.config.form[k];

				this.dom.elements[name] = jQuery( 'input[name="' + k + '"]', this.dom.form );

				if (this.dom.elements[name].length == 0) {
					this.dom.elements[name] = jQuery( 'select[name="' + k + '"]', this.dom.form );
				}

				if (this.dom.elements[name].length == 0) {
					this.dom.elements[name] = jQuery( 'textarea[name="' + k + '"]', this.dom.form );
				}
			}

			// email and membership insert txt
			self.dom.elements['Membership Type'].parent().append("<p>" + jQuery('label img', self.dom.elements['Membership Type'].parent().parent()).data('content') + "</p>");

			// update validation
			//self.dom.elements['Affiliation'  ].prop('maxlength',  '250');
			self.dom.elements['E-mail'       ].prop('maxlength',  '100');
			self.dom.elements['First name'   ].prop('maxlength',  '100');
			self.dom.elements['Surname'      ].prop('maxlength',  '100');
			//self.dom.elements['Matriculation Year'].prop('maxlength',    '4');
			self.dom.elements['Message'      ].prop('maxlength', '4000');
			self.dom.elements['School'       ].prop('maxlength',  '100');

			// event handlers
			this.dom.elements['Membership Type'].on( 'change', null, {self: self}, function (evnt) {evnt.data.self.onchange_membership(evnt); } );

			// startup
			//thingy.onchange_membership();
		};

		/**
		 * event handler
		 */
		thingy.onchange_membership = function (evnt) {
            const self = this;
            const val = $(evnt.target).val();
            const picked = $(`#${evnt.target.id} option:selected`).text().trim().toLowerCase();

            hideRoleBasedFields();
            let fields;
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
		window.l4p = window.l4p || {config: {}, modules: {}};
		window.l4p.modules.form = thingy;

		// initialise
		window.setTimeout(
			function () {
				thingy.init();
			},
			2000
		);
	}
)();

function hideRoleBasedFields() {
    let typeBasedFields = [
        'school_information', 'professional_information'
    ];
    typeBasedFields.forEach(item => {
        regFormFields.form[item].forEach(i => {
            $('.form-control--' + i.name.toLowerCase())
                .removeAttr('required')
                .closest('.form-group')
                .hide()
                .removeClass('admidio-form-group-required');
        });
    });
}


/** #
 * @author: Akshay
 * @since: 5 June 2018
 */
$(document).ready(function() {
    hideRoleBasedFields();
});


function showFields(fields) {
    fields.forEach(i => {
        regFormFields.form[i].forEach(xField => {
            $('.form-control--' + xField.name.toLowerCase())
                .prop('required', true)
                .closest('.form-group')
                .show()
                .addClass('admidio-form-group-required');
        });
    });
}
/** Validating inputs for registration form: Ends here */
