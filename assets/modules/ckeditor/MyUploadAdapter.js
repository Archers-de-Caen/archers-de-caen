class MyUploadAdapter {
    constructor( loader ) {
        // The file loader instance to use during the upload.
        this.loader = loader;
    }

    upload() {
        return this.loader.file.then( file => {
            const body = new FormData()
            body.append('imageFile', file)

            return fetch('/api/photos', {
                method: 'POST',
                body
            }).then( response => {
                return response.json()
            }).then( json => {
                return {
                    urls: {
                        default: '/images/photo/' + json.imageName
                    }
                }
            })
        } )
    }

    // Aborts the upload process.
    abort() {
        alert('Une erreur est survenu')
    }
}

export default MyUploadAdapter