const importModal = document.querySelector('#modal-import-action');

importModal.addEventListener('show.bs.modal', function (event) {
    let button = event.relatedTarget

    let formAction = button.getAttribute('data-bs-form-action-href')
    let modalDialog = importModal.querySelector('.modal-dialog')
    modalDialog.setAttribute('action', formAction)

    let csvModel = button.getAttribute('data-bs-csv-model-href')
    let csvModelLink = modalDialog.querySelector('#modal-import-csv-model')
    csvModelLink.setAttribute('href', csvModel)
})
