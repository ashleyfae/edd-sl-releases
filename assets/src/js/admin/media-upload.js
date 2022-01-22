/* global eddSlReleases */

export function mediaButtonEvent() {
    const mediaButtons = document.querySelectorAll( '.edd-sl-releases--upload' );

    if ( ! mediaButtons ) {
        return;
    }

    mediaButtons.forEach( button => {
        button.addEventListener( 'click', e => {
            e.preventDefault();

            const fileIdEl = document.getElementById(
                button.getAttribute( 'data-id-el' )
            );

            if ( ! fileIdEl ) {
                console.log( 'Missing file ID element.' );
                return;
            }

            const mediaFrame = wp.media( {
                title: eddSlReleases.uploadReleaseFile,
                button: {text: eddSlReleases.selectFile},
                multiple: false
            } );

            mediaFrame.open();

            mediaFrame.on( 'select', () => {
                const selection = mediaFrame.state().get( 'selection' );
                selection.map( attachment => {
                    attachment.toJSON();

                    console.log( 'attachment', attachment );

                    if ( attachment.id ) {
                        fileIdEl.value = attachment.id;
                    }

                    if ( attachment.attributes && attachment.attributes.filename ) {
                        const fileNameEl = document.getElementById( 'edd-sl-releases-file-name' );
                        if ( fileNameEl && ! fileNameEl.value ) {
                            fileNameEl.value = attachment.attributes.filename;
                        }
                    }
                } );
            } );
        } );
    } );
}
