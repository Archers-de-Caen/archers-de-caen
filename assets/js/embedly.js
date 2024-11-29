document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll( 'oembed[url]' ).forEach( element => {
        // ignore pdfs
        if (element.getAttribute('url').includes('.pdf')) {
            return
        }

        // Create the <a href="..." class="embedly-card"></a> element that Embedly uses
        // to discover the media.
        const anchor = document.createElement( 'a' )

        anchor.setAttribute( 'href', element.getAttribute( 'url' ) )
        anchor.className = 'embedly-card'

        element.appendChild( anchor )
    } )
} )
