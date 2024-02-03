import Swal from "sweetalert2";

window.addEventListener('load', function() {
    document.querySelectorAll('.flash').forEach(function(flash) {
        let title = ''
        let text = flash.value
        let type = flash.dataset.type
        let imageUrl = ''
        let date = ''

        if ('success' === flash.dataset.type) {
            title = 'En plein dans le mille ! 🎯'
        } else if ('error' === flash.dataset.type || 'danger' === flash.dataset.type) {
            title = 'Manqué ! 😿'
        } else if ('popup' === flash.dataset.type) {
            const flashDecoded = JSON.parse(flash.value)

            title = flashDecoded.title
            text = flashDecoded.content
            imageUrl = flashDecoded.image
            date = flashDecoded.date
            type = null
        }

        Swal.fire({
            title,
            text,
            icon: type,
            imageUrl,
            confirmButtonText: 'OK',
            confirmButtonColor: '#FDD20E',
        }).then(() => {
            if (flash.dataset.type === 'popup') {
                const dateInAMonth = (new Date()).setMonth(new Date().getMonth() + 1)

                document.cookie = `popup=${date}; expires=${dateInAMonth.toString()}; path=/;`
            }
        }).catch((error) => {
            console.log(error)
        })
    })
})

