document.querySelectorAll('.carousels-prev, .carousels-next').forEach(function (btn) {
    btn.addEventListener('click', function (event) {
        const target = event.currentTarget
        let left = 150

        if (target.classList.contains('carousels-prev')) {
            left = -150
        }

        target.closest('.carousels-container').querySelector('.carousels').scrollBy({
            top: 0,
            left: left,
            behavior: 'smooth'
        })
    })
})

