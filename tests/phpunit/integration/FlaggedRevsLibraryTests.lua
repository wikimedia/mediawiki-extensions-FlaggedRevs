return require( 'Module:TestFramework' ).getTestProvider( {

	{ name = 'stability settings for page with FlaggedRevs disabled', func = mw.ext.FlaggedRevs.getStabilitySettings,
	args = { mw.title.new( 'Page without FR' ) }, expect = { { override = 0, autoreview = '', expiry = 'infinity' } }
	},

	{ name = 'stability settings for page with FlaggedRevs enabled', func = mw.ext.FlaggedRevs.getStabilitySettings,
	args = { mw.title.new( 'Page with FR' ) }, expect = { { override = 1, autoreview = 'autoconfirmed', expiry = '20370101000000' } }
	},

} )
