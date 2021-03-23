/* eslint-env node */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			options: {
				cache: true,
				fix: grunt.option( 'fix' )
			},
			all: [
				'**/*.{js,json}',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		stylelint: {
			all: [
				'**/*.css',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		banana: {
			api: 'i18n/api/',
			configuredpages: 'i18n/configuredpages/',
			flaggedrevs: 'i18n/flaggedrevs/',
			pendingchanges: 'i18n/pendingchanges/',
			qualityoversight: 'i18n/qualityoversight/',
			revisionreview: 'i18n/revisionreview/',
			stabilization: 'i18n/stabilization/',
			stablepages: 'i18n/stablepages/',
			unreviewedpages: 'i18n/unreviewedpages/',
			validationstatistics: 'i18n/validationstatistics/',
			options: {
				requireLowerCase: 'initial'
			}
		}
	} );

	grunt.registerTask( 'test', [ 'eslint', 'stylelint', 'banana' ] );
	grunt.registerTask( 'default', 'test' );
};
