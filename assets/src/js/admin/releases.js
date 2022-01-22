/* global eddSlReleases */

import apiRequest from "../utils/api";
import {parseErrorMessage} from "../utils/errors";

export function renderProductReleases() {
    const wrapper = document.getElementById( 'edd-sl-releases' );
    if ( ! wrapper ) {
        return;
    }

    const productId = wrapper.getAttribute( 'data-product' );
    if ( ! productId ) {
        return;
    }

    wrapper.innerHTML = '<p>' + eddSlReleases.loadingReleases + '</p>';

    apiRequest( 'products/' + productId + '/releases' )
        .then( response => {
            if ( ! response.releases || ! response.releases.length ) {
                wrapper.innerHTML = '<p>' + eddSlReleases.noReleases + '</p>';
            } else {
                wrapper.innerHTML = response.releases.map( buildReleaseMarkup ).join( '' );
            }
        } )
        .catch( error => {
            console.log( 'Error fetching releases', error );

            error.json().then( response => {
                wrapper.innerText = parseErrorMessage( response );
            } )
        } )
}

function buildReleaseMarkup( release ) {
    let preRelease = '';

    if ( release.pre_release ) {
        preRelease = `<span class="edd-sl-releases--pre-release">${eddSlReleases.preRelease}</span>`;
    }

    return `
<div class="edd-sl-releases--release" data-id="${release.id}">
    <div class="edd-sl-releases--release--header">
        <h4>
            ${release.version}
            ${preRelease}
        </h4>
        <span class="edd-sl-releases--release--date">
            ${release.created_at_display}
        </span>    
    </div>
    <div class="edd-sl-releases--release--body">
        <div class="edd-form-group edd-form-group__control">
            <label
                for="release-${release.id}-changelog"
                class="edd-form-group__label"
            >${eddSlReleases.changelog}</label>
            <textarea
                id="release-${release.id}-changelog"
                rows="5"
            >${release.changelog || ''}</textarea>
        </div>
    </div>
</div>
    `;
}
