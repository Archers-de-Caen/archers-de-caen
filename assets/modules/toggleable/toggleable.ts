function addEventListenerOnToggleableElement(toggleable: Element) {
  toggleable.addEventListener('click', function (event: Event) {
    for (const path of event.composedPath()) {
      if (!(path instanceof Element)) {
        continue
      }

      if (path.classList.contains('toggleable-content')) {
        return
      }
    }

    const target = event.currentTarget

    if (!(target instanceof Element)) {
      return
    }

    target.classList.toggle('--active')
  })
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.toggleable').forEach(addEventListenerOnToggleableElement)

  const observer = new MutationObserver((mutations: MutationRecord[]) => {

    /** @var mutations est un tableau des changements du dom */
    mutations.forEach((mutation: MutationRecord) => {
      if (!mutation.addedNodes.length) {
        return
      }

      mutation.addedNodes.forEach((node: Node) => {
        if (!(node instanceof Element)) {
          return
        }

        if (node.classList.contains('toggleable')) {
          addEventListenerOnToggleableElement(node)
        }
      })
    })
  })


// On précise ici que l'observer ne veut observer que les éléments qui sont dans le body
  observer.observe(document.body, {
    childList: true, // L’ajout ou la suppression des éléments enfants du nœud visé (incluant les nœuds de texte) sont à observer.
    subtree: true // Les descendants du nœud visé sont également à observer.
  })
})
