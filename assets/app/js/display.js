export function hide(element) {
    element.style.display = 'none'

    element.querySelectorAll('input, select').forEach((input) => {
        input.required = false
    })
}

export function show(element, type = 'block') {
    element.style.display = type

    element.querySelectorAll('input, select').forEach((input) => {
        input.required = true
    })
}

window.hide = hide
window.show = show
