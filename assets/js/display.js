export function hide(element) {
    if (!element) {
        return
    }

    element.style.display = 'none'

    element.querySelectorAll('input, select').forEach((input) => {
        input.required = false
    })
}

export function show(element, type = 'block') {
    if (!element) {
        return
    }

    element.style.display = type

    element.querySelectorAll('input, select').forEach((input) => {
        input.required = true
    })
}

global.hide = window.hide = hide
global.show = window.show = show
