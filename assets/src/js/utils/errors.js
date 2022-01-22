/* global eddSlReleases */

export function parseErrorMessage( error ) {
    let errorMessage = eddSlReleases.defaultError;
    if ( error.message ) {
        errorMessage = error.message;
    } else if ( error.error ) {
        errorMessage = error.error;
    } else if ( error.status && error.statusText ) {
        errorMessage = error.status + ": " + error.statusText;
    }

    return errorMessage;
}
