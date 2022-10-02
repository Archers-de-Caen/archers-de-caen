document.querySelectorAll('.toggleable').forEach(function (toggleable) {
  toggleable.addEventListener('click', function (event) {
    event.currentTarget.classList.toggle('--active')
  })
})

