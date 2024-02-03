window.onload = function() {
    const filesInput = document.querySelector('#gallery-photo')
    const output = document.querySelector(".gallery-photo-previews table tbody")

    if (!filesInput || !output) {
        return
    }

    filesInput.addEventListener("change", function(event) {
        const files = event.target.files // FileList object

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

            fetch('/api/photos', {
                method: 'POST',
                body
            }).then( response => {
                response.json().then(json => {
                    if (response.status === 201) {
                        const input = document.createElement('input')
                        input.name = document.querySelector('.gallery-input-name').value
                        input.value = json.token
                        input.type = 'hidden'
                        document.querySelector('.photos-token-list').appendChild(input)

                        newTdName.innerText = json.imageOriginalName
                        newTr.setAttribute('data-photo-token', json.token)

                        const photoPreview = document.createElement('div')
                        photoPreview.classList.add('photo-preview')

                        const photoPreviewLink  = document.createElement('a')
                        photoPreviewLink.setAttribute('href', json.url)
                        photoPreviewLink.innerText = json.url
                        photoPreview.appendChild(photoPreviewLink)

                        const photoPreviewImg  = document.createElement('img')
                        photoPreviewImg.setAttribute('src', json.url)
                        photoPreviewImg.setAttribute('loading', 'lazy')
                        photoPreviewImg.setAttribute('alt', 'preview ' + json.imageOriginalName)
                        photoPreview.appendChild(photoPreviewImg)
                        newTdUrl.appendChild(photoPreview)

                        const trash = document.createElement('i')
                        trash.classList.add('fa')
                        trash.classList.add('fa-trash')
                        trash.addEventListener('click', function (e) {
                            removePhoto(e.currentTarget.closest('.gallery-photo-preview'))
                        })
                        newTdAction.appendChild(trash)
                    } else {
                        removePhoto(newTr)

                        alert(json.message)
                    }
                }).catch(error => alert(error))
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
