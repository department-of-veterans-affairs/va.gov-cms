'use strict';
module.exports = function ( grunt ) {

	require( 'load-grunt-tasks' )( grunt );

	// Project configuration
	grunt.initConfig( {

		// Configure ESLint task
		eslint: {
			all: [
				'**/*.js',
				'!node_modules/**'
			]
		},

		// Configure JSONLint task
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**'
			]
		},

		// Configure Nightwatch task
		nightwatch: {
			options: {
				// nightwatch settings
				src_folders: [ 'tests' ], // eslint-disable-line camelcase
				output_folder: 'report' // eslint-disable-line camelcase
			}
		}

	} );

	// Default tasks
	grunt.registerTask( 'default', [ 'eslint', 'jsonlint' ] );

};
