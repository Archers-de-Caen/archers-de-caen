document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll( '.open-native-share' ).forEach( element => {
        element.addEventListener('click', function (e) {
            const target = e.currentTarget
            e.preventDefault()

            navigator.share({
                title: target.getAttribute('data-title'),
                text: target.getAttribute('data-text'),
                url: target.getAttribute('data-url')
            }).then(() => {
                console.log('Thanks for sharing!')
            }).catch((error) => {
                console.log('Error sharing', error)
            })
        })
    })
} )
