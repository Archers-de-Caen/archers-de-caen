window.onload = function() {
    // Check File API support
    if (window.File && window.FileList && window.FileReader) {
        const filesInput = document.getElementById("gallery-photo")

        if (filesInput) {
            filesInput.addEventListener("change", function(event) {
                const files = event.target.files // FileList object
                const output = document.querySelector(".gallery-photo-previews")

                for (let i = 0; i < files.length; i++) {
                    const loading = document.createElement('div')
                    loading.innerText = 'upload ...'
                    loading.classList.add('loading')

                    const file = files[i]
                    const div = document.createElement("div")
                    div.classList.add('gallery-photo-preview')

                    // Only pics
                    if (!file.type.match('image')) {
                        continue
                    }

                    const body = new FormData()
                    body.append('imageFile', file)

                    div.appendChild(loading)

                    fetch('/api/photos', {
                        method: 'POST',
                        body
                    }).then( response => {
                        response.json().then(json => {
                            if (response.status === 201) {
                                div.querySelector('.loading').remove()

                                const trash = document.createElement('i')
                                trash.classList.add('fa')
                                trash.classList.add('fa-trash')

                                div.appendChild(trash)
                                div.setAttribute('data-photo-token', json.token)

                                const input = document.createElement('input')
                                input.name = document.querySelector('.gallery-input-name').value + '[]'
                                input.value = json.token
                                input.type = 'hidden'
                                output.appendChild(input)

                                trash.addEventListener('click', function (e) {
                                    removePhoto(e.currentTarget.closest('.gallery-photo-preview'))
                                })
                            } else {
                                removePhoto(div)

                                alert(json.message)
                            }
                        }).catch(error => alert(error))
                    }).catch(error => alert(error))

                    const picReader = new FileReader()
                    picReader.addEventListener("load", function(event) {
                        const picFile = event.target

                        const img = document.createElement('img')
                        img.setAttribute('src', picFile.result)
                        img.setAttribute('title', picFile.name)
                        img.setAttribute('alt', picFile.name)

                        div.appendChild(img)
                        output.insertBefore(div, null)
                    })

                    // Read the image
                    picReader.readAsDataURL(file)
                }

                filesInput.value = null
            })
        }
    } else {
        console.log("Your browser does not support File API")
    }

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