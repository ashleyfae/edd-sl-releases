/* global eddSlReleases */

export default function apiRequest( endpoint, method = 'GET', body = {} ) {
    const args = {
        method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': eddSlReleases.restNonce
        }
    }

    if ( Object.keys( body ).length ) {
        args.body = JSON.stringify( body );
    }

    return fetch( eddSlReleases.restBase + endpoint, args )
        .then( response => {
            if ( ! response.ok ) {
                return Promise.reject( response );
            }

            return response.json();
        } );
}
