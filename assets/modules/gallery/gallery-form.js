window.onload = function() {
    const galleryForm = document.querySelector('.gallery-form')

    if (!galleryForm) {
        return
    }

    const filesInput = document.querySelector('#gallery-photo')
    const output = document.querySelector(".gallery-photo-previews table tbody")
    const galleryId = galleryForm.dataset.galleryId

    if (!filesInput || !output) {
        return
    }

    fetchPhotos(galleryId, output)

    filesInput.addEventListener("change", async function(event) {
        const files = event.target.files // FileList object

        // start progress bar
        const progressBarContainer = galleryForm.querySelector('.gallery-photo-progress-container')
        const progressBar = progressBarContainer.querySelector('.gallery-photo-progress')
        progressBar.value = 0
        progressBar.setAttribute('max', files.length)

        const progressCounter = progressBarContainer.querySelector('.gallery-photo-progress-count')
        progressCounter.innerText = '0'

        const progressTotal = progressBarContainer.querySelector('.gallery-photo-progress-total')
        progressTotal.innerText = files.length

        for (let file of files) {
            const newTr = document.createElement('tr')
            newTr.classList.add('gallery-photo-preview')

            const newTdName = document.createElement('td')
            const newTdUrl = document.createElement('td')
            const newTdAction = document.createElement('td')

            // Only pics
            if (!file.type.match('image')) {
                continue
            }

            const body = new FormData()
            body.append('imageFile', file)

            newTdName.innerText = 'Chargement ...'

            newTr.appendChild(newTdName)
            newTr.appendChild(newTdUrl)
            newTr.appendChild(newTdAction)

            output.appendChild(newTr)

            const response = await fetch('/api/photos?gallery='+galleryId, {
                method: 'POST',
                body
            })

            response.json().then(json => {
                if (response.status === 201) {
                    addPhotoOnDom(json, newTdName, newTr, newTdUrl, newTdAction)

                    progressBar.value++
                    progressCounter.innerText = progressBar.value
                } else {
                    removePhoto(newTr)

                    alert(json.message)
                }
            }).catch(error => alert(error))
        }

        filesInput.value = null
    })

    document.querySelectorAll('.gallery-photo-preview .fa-trash').forEach(element => {
        element.addEventListener('click', (trash) => {
            removePhoto(trash.currentTarget.closest('.gallery-photo-preview'))
        })
    })
}

function removePhoto(element) {
    if (element.getAttribute('data-photo-token')) {
        fetch('/api/photos/' + element.getAttribute('data-photo-token'), {
            method: 'DELETE',
        }).then(response => {
            if (response.status === 204) {
                element.remove()
            } else {
                response.json().then(json => {
                    alert(json.message)
                })
            }
        })
    } else {
        element.remove()
    }
}

function addPhotoOnDom(data, newTdName, newTr, newTdUrl, newTdAction) {
    newTdName.innerText = data.imageOriginalName
    newTr.setAttribute('data-photo-token', data.token)

    const photoPreview = document.createElement('div')
    photoPreview.classList.add('photo-preview')

    const photoPreviewLink  = document.createElement('a')
    photoPreviewLink.setAttribute('href', data.url)
    photoPreviewLink.innerText = data.url
    photoPreview.appendChild(photoPreviewLink)

    const photoPreviewImg  = document.createElement('img')
    photoPreviewImg.setAttribute('src', data.url)
    photoPreviewImg.setAttribute('loading', 'lazy')
    photoPreviewImg.setAttribute('alt', 'preview ' + data.imageOriginalName)
    photoPreview.appendChild(photoPreviewImg)
    newTdUrl.appendChild(photoPreview)

    const trash = document.createElement('i')
    trash.classList.add('fa')
    trash.classList.add('fa-trash')
    trash.addEventListener('click', function (e) {
        removePhoto(e.currentTarget.closest('.gallery-photo-preview'))
    })

    newTdAction.appendChild(trash)
}

function fetchPhotos(galleryId, outputElement) {
    fetch('/api/photos?gallery='+galleryId).then(response => {
        response.json().then(json => {
            json.forEach(photo => {
                const newTr = document.createElement('tr')
                newTr.classList.add('gallery-photo-preview')

                const newTdName = document.createElement('td')
                const newTdUrl = document.createElement('td')
                const newTdAction = document.createElement('td')

                newTr.appendChild(newTdName)
                newTr.appendChild(newTdUrl)
                newTr.appendChild(newTdAction)

                outputElement.appendChild(newTr)

                addPhotoOnDom(photo, newTdName, newTr, newTdUrl, newTdAction)
            })
        })
    })
}
