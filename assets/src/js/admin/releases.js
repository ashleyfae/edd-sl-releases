/* global eddSlReleases */

import apiRequest from "../utils/api";
import {parseErrorMessage} from "../utils/errors";

export function renderProductReleases() {
    const wrapper = document.getElementById( 'edd-sl-releases' );
    const loading = document.getElementById( 'edd-sl-releases-loading' );
    const noReleases = document.getElementById( 'edd-sl-releases-none' );
    const listReleases = document.getElementById( 'edd-sl-releases-list' );
    if ( ! wrapper || ! listReleases ) {
        return;
    }

    const productId = wrapper.getAttribute( 'data-product' );
    if ( ! productId ) {
        return;
    }

    apiRequest( 'products/' + productId + '/releases' )
        .then( response => {
            if ( ! response.releases || response.releases.length === 0 ) {
                if ( noReleases ) {
                    noReleases.classList.remove( 'hidden' );
                }
            } else {
                listReleases.innerHTML = response.releases.map( buildReleaseMarkup ).join( '' );
                listReleases.classList.remove( 'hidden' );
            }
        } )
        .catch( error => {
            console.log( 'Error fetching releases', error );

            error.json().then( response => {
                const errorWrap = document.getElementById( 'edd-sl-releases-errors' );

                if ( errorWrap ) {
                    errorWrap.innerText = parseErrorMessage( response );
                    errorWrap.classList.remove( 'hidden' );
                }
            } )
        } )
        .finally( () => {
            if ( loading ) {
                loading.classList.add( 'hidden' );
            }
        } )
}

function buildReleaseMarkup( release ) {
    let releaseType = '';
    if ( release.pre_release ) {
        releaseType = `<span class="edd-sl-releases-badge edd-sl-releases-badge--pre-release">${eddSlReleases.preRelease}</span>`;
    } else {
        releaseType = `<span class="edd-sl-releases-badge edd-sl-releases-badge--stable">${eddSlReleases.stableRelease}</span>`;
    }

    return `
<div class="edd-sl-releases--release" data-id="${release.id}">
    <div class="edd-sl-releases--release--header">
        <h4>
            <span class="edd-sl-releases--release--version">${release.version}</span>
            ${releaseType}
            <span class="edd-sl-releases--release--date">
                &ndash;
                ${release.released_at_display}
            </span>
        </h4>
        <div class="edd-sl-releases--release--actions">
            <a href="${release.edit_url}" class="button button-secondary">
                ${eddSlReleases.edit}
            </a>
        </div>    
    </div>
</div>
    `;
}
