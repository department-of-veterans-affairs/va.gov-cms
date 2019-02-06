// createAccount( username, password ) {

'use strict';

var createAccountCommands = {
	createAccount: function() {
		return this.navigate()
			.setValue( '@username', 'test1' )
			.setValue( '@password', 'test2' )
			.setValue( '@confirmPassword', 'test2' )
			.click( '@create' );
	}
};

module.exports = {
	url: function() {
		return this.api.launchUrl + 'Special:CreateAccount';
	},
	elements: {
		username: '#wpName2',
		password: '#wpPassword2',
		confirmPassword: '#wpRetype',
		create: '#wpCreateaccount',
		heading: '#firstHeading'
	},
	commands: [ createAccountCommands ]
};
