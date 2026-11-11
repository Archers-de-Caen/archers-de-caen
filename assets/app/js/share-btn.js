function openInNewTab(href) {
    const width = 580
    const height = 290

    window.open(href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,width=' + width + ',height=' + height)
}

window.openInNewTab = openInNewTab
