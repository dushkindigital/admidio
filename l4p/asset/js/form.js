/**
 * reg form wire up
 */
(
	function () {
		
		console.log( 'BANG' );
		
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
			self.dom.elements['E-mail'    ].parent().append("<p>" + jQuery('label img', self.dom.elements['E-mail'    ].parent().parent()).data('content') + "</p>");
			self.dom.elements['Membership'].parent().append("<p>" + jQuery('label img', self.dom.elements['Membership'].parent().parent()).data('content') + "</p>");
			
			// update validation
			self.dom.elements['Affiliation'  ].prop('maxlength',  '250');
			self.dom.elements['E-mail'       ].prop('maxlength',  '100');
			self.dom.elements['First name'   ].prop('maxlength',  '100');
			self.dom.elements['Surname'      ].prop('maxlength',  '100');
			self.dom.elements['Matriculation'].prop('maxlength',    '4');
			self.dom.elements['Message'      ].prop('maxlength', '4000');
			
			// event handlers
			this.dom.elements['Membership'].on( 'change', null, {self: self}, function (evnt) {evnt.data.self.onchange_membership(evnt); } );
			
			// startup
			thingy.onchange_membership();
		};
		
		/**
		 * event handler
		 */
		thingy.onchange_membership = function (evnt) {
			
			const self = this;
			
			const val = self.dom.elements['Membership'].val();
			
			const picked = jQuery( 'option[value="' + val + '"]', self.dom.elements['Membership'] ).text();
			
			self.dom.elements['Affiliation'  ].parent().parent().hide();
			self.dom.elements['Matriculation'].parent().parent().hide();
			self.dom.elements['School'       ].parent().parent().hide();
			
			self.dom.elements['Affiliation'  ].parent().parent().removeClass('admidio-form-group-required');
			self.dom.elements['Matriculation'].parent().parent().removeClass('admidio-form-group-required');
			self.dom.elements['School'       ].parent().parent().removeClass('admidio-form-group-required');
			
			self.dom.elements['Affiliation'  ].removeAttr('required');
			self.dom.elements['Matriculation'].removeAttr('required');
			self.dom.elements['School'       ].removeAttr('required');
			
			if (picked == 'Associate') {
				self.dom.elements['Affiliation'].parent().parent().show();
				
				self.dom.elements['Affiliation'].prop('required', true);
				self.dom.elements['Affiliation'].parent().parent().addClass('admidio-form-group-required');
				
			}
			
			if (picked == 'Member') {
				self.dom.elements['Matriculation'].parent().parent().show();
				self.dom.elements['School'       ].parent().parent().show();
				
				self.dom.elements['Matriculation'].prop('required', true);
				self.dom.elements['School'       ].prop('required', true);
				
				self.dom.elements['Matriculation'].parent().parent().addClass('admidio-form-group-required');
				self.dom.elements['School'       ].parent().parent().addClass('admidio-form-group-required');
			}
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
