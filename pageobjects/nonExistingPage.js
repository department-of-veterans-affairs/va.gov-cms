'use strict';

var pageCommands = {
	create: function( content ) {
		return this.navigate()
			.setValue( '@content', content )
			.click( '@save' );
	}
};

module.exports = {
	url: function() {
		var name = Math.random().toString();
		return this.api.launchUrl + name + '&action=edit';
	},
	elements: {
		content: '#wpTextbox1',
		displayedContent: '#mw-content-text',
		heading: '#firstHeading',
		save: '#wpSave'
	},
	commands: [ pageCommands ]
};
