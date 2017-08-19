/* eslint-env node */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			all: [
				'**/*.js',
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
			problemchanges: 'i18n/problemchanges/',
			qualityoversight: 'i18n/qualityoversight/',
			reviewedpages: 'i18n/reviewedpages/',
			reviewedversions: 'i18n/reviewedversions/',
			revisionreview: 'i18n/revisionreview/',
			stabilization: 'i18n/stabilization/',
			stablepages: 'i18n/stablepages/',
			unreviewedpages: 'i18n/unreviewedpages/',
			validationstatistics: 'i18n/validationstatistics/'
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**',
				'!vendor/**'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'eslint', 'stylelint', 'jsonlint', 'banana' ] );
	grunt.registerTask( 'default', 'test' );
};
