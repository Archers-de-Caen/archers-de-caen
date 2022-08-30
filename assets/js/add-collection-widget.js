document.querySelectorAll('.add-another-collection-widget').forEach(add => {
    add.addEventListener('click', (e) => {
        const target = e.currentTarget

        const list = document.querySelector(target.getAttribute('data-list-selector'))

        // Try to find the counter of the list or use the length of the list
        let counter = list.getAttribute('data-widget-counter') || list.children().length

        // grab the prototype template
        let newWidget = list.getAttribute('data-prototype')

        // replace the "__name__" used in the id and name of the prototype
        // with a number that's unique to your emails
        // end name attribute looks like name="contact[emails][2]"
        const prototypeName = list.getAttribute('data-prototype-name') || '__name__'
        newWidget = newWidget.replaceAll(prototypeName, counter)

        // Increase the counter
        counter++

        // And store it, the length cannot be used if deleting widgets is allowed
        list.setAttribute('data-widget-counter', counter)

        // create a new list element and add it to the list
        const placeholder = document.createElement("div")
        placeholder.innerHTML = list.getAttribute('data-widget-tags')
        const newElem = placeholder.firstElementChild

        newElem.innerHTML = newWidget
        list.append(newElem)
    })
})
