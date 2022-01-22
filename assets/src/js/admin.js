const {renderProductReleases} = require( "./admin/releases" );
const {mediaButtonEvent} = require( "./admin/media-upload" );

document.addEventListener( 'DOMContentLoaded', () => {
    renderProductReleases();
    mediaButtonEvent();
} );
