document.querySelectorAll('.toggleable').forEach(function (toggleable) {
  toggleable.addEventListener('click', function (event) {
    for (const path of event.composedPath()) {
      if (path.classList && path.classList.contains('toggleable-content')) {
        return
      }
    }

    event.currentTarget.classList.toggle('--active')
  })
})
