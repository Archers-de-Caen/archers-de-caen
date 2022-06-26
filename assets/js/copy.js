document.querySelectorAll('.copy-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        const target = e.currentTarget

        e.preventDefault()

        copyToClipboard(target.getAttribute('data-copy') ? target.getAttribute('data-copy') : target.getAttribute('href')).then(function () {
            let validateCopy

            if (target.getAttribute('data-hover')) {
                const dataHover = target.getAttribute('data-hover')
                target.setAttribute('data-hover', '✔')

                validateCopy = () => target.setAttribute('data-hover', dataHover)
            } else {
                const innerHtml = target.innerHTML
                target.innerHTML = '<span>✔</span>'

                validateCopy = () => target.innerHTML = innerHtml
            }

            setTimeout(validateCopy, 2000)
        })
    })
})

function copyToClipboard(href)
{
    if (navigator.clipboard && window.isSecureContext) {
        // navigator clipboard api method'
        return navigator.clipboard.writeText(href);
    } else {
        // text area method
        let textArea = document.createElement("textarea");
        textArea.value = href;
        // make the textarea out of viewport
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        return new Promise((res, rej) => {
            // here the magic happens
            document.execCommand('copy') ? res() : rej();
            textArea.remove();
        });
    }
}

global.copyToClipboard = copyToClipboard;
