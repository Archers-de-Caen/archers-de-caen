function fitHeight(element) {
    // +50 je ne sais pas d'où ça sort, mais ça marche

    if (element.contentDocument) {
        element.height = element.contentDocument.body.scrollHeight + 50 + 'px'
    }
}

function fitWidth(element) {
    if (element.contentDocument.body) {
        element.width = element.contentDocument.body.scrollWidth + 'px'
    }
}

function fit(element) {
    fitHeight(element)
    fitWidth(element)
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.fit-height-content').forEach((element) => {
        element.addEventListener('load', () => fitHeight(element))
    })

    document.querySelectorAll('.fit-width-content').forEach((element) => {
        element.addEventListener('load', () => fitWidth(element))
    })

    document.querySelectorAll('.fit-content').forEach((element) => {
        element.addEventListener('load', () => fit(element))
    })
})

window.addEventListener('resize', () => {
    document.querySelectorAll('.fit-height-content').forEach((element) => fitHeight(element))
    document.querySelectorAll('.fit-width-content').forEach((element) => fitWidth(element))
    document.querySelectorAll('.fit-content').forEach((element) => fit(element))
})
