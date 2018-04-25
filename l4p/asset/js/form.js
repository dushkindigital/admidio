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
				
				console.log( k );
				
				this.dom.elements[name] = jQuery( 'input[name="' + k + '"]', this.dom.form );
				
				if (this.dom.elements[name].length == 0) {
					this.dom.elements[name] = jQuery( 'select[name="' + k + '"]', this.dom.form );
				}
				
				if (this.dom.elements[name].length == 0) {
					this.dom.elements[name] = jQuery( 'textarea[name="' + k + '"]', this.dom.form );
				}
			}
			
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
			
			console.log( val, picked );
			
			self.dom.elements['Affiliation'  ].parent().parent().hide();
			self.dom.elements['Matriculation'].parent().parent().hide();
			self.dom.elements['School'       ].parent().parent().hide();
			
			if (picked == 'Associate') {
				self.dom.elements['Affiliation'  ].parent().parent().show();
			}
			
			if (picked == 'Member') {
				self.dom.elements['Matriculation'].parent().parent().show();
				self.dom.elements['School'       ].parent().parent().show();
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
