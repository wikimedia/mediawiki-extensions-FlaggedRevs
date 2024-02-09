'use strict';

const { action, assert, REST, utils } = require( 'api-testing' );

const testPage = utils.title( 'testPage' );

describe( 'DiffHeaderItems', () => {
	const client = new REST( 'rest.php/flaggedrevs/internal' );
	let oldId, newId, alice;

	before( async () => {
		alice = await action.alice();
		const edit1 = await alice.edit( testPage, { text: utils.uniq() } );
		const edit2 = await alice.edit( testPage, { text: utils.uniq() } );
		oldId = edit1.newrevid;
		newId = edit2.newrevid;
	} );

	describe( 'GET diffheaderitems', () => {
		it( 'diff header response that return html snippet', async () => {
			const { status: statusCode, headers: headers } = await client.get( `/diffheader/${ oldId }/${ newId }` );
			assert.equal( statusCode, 200 );
			assert.equal( headers[ 'content-type' ], 'text/html;charset=UTF-8' );
		} );
	} );
} );
