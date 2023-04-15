import Swal from "sweetalert2";

window.addEventListener('load', function() {
    document.querySelectorAll('.flash').forEach(function(flash) {
        let title = ''
        if (flash.dataset.type === 'success') {
            title = 'En plein dans le mille ! 🎯'
        } else if (flash.dataset.type === 'error' || flash.dataset.type === 'danger') {
            title = 'Manqué ! 😿'
        }

        Swal.fire({
            title: title,
            text: flash.value,
            type: flash.dataset.type,
            confirmButtonText: 'OK',
            confirmButtonColor: '#FDD20E',
        })
    });
})
