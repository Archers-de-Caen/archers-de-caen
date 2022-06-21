document.querySelectorAll('.fit-height-content').forEach((element) => {
    element.addEventListener('load', () => {
        element.height = element.contentDocument.body.scrollHeight + 50 + 'px' // +50 je ne sais pas d'où ça sort, mais ça marche
    })
})

document.querySelectorAll('.fit-width-content').forEach((element) => {
    element.addEventListener('load', () => {
        element.width = element.contentDocument.body.scrollWidth + 'px'
    })
})

document.querySelectorAll('.fit-content').forEach((element) => {
    element.addEventListener('load', () => {
        element.height = element.contentDocument.body.scrollHeight + 50 + 'px' // +50 je ne sais pas d'où ça sort, mais ça marche
        element.width = element.contentDocument.body.scrollWidth + 'px'
    })
})
