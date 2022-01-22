require( './admin/releases' );
const {renderProductReleases} = require( "./admin/releases" );

document.addEventListener( 'DOMContentLoaded', () => {
    renderProductReleases();
} );
