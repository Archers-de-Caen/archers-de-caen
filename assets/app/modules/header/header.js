headerSize()

window.onscroll = headerSize

function headerSize() {
    // Correspond au menu principal, contenant le logo et les liens principaux (sous fond blanc)
    const headerPrimary = document.querySelector(".header .header-primary")
    if (headerPrimary === undefined) {
        return
    }

    // Correspond au menu secondaire (sous fond d'un dégradé des couleurs d'une cible)
    const headerSecondary = document.querySelector(".header .header-secondary")
    if (headerSecondary === undefined) {
        return
    }

    // Correspond au menu burger (seulement affiché sur les petits écrans)
    const menuResponsiveBtn = headerPrimary.querySelector('.menu-responsive-btn')

    // headerPrimary.querySelector('ul').style.top = headerPrimary.style.height

    if (
        document.body.scrollTop > headerSecondary.clientHeight ||
        document.documentElement.scrollTop > headerSecondary.clientHeight
    ) {
        // Si la barre de scroll est descendu

        // Fix le menu principal tout en haut
        headerPrimary.style.height = '50px'
        headerPrimary.style.position = 'fixed'
        headerPrimary.style.top = '0'
        headerPrimary.style.left = '0'
        headerPrimary.style.right = '0'

        // Change la taille du logo
        headerPrimary.querySelector('img').style.height = '40px'

        // Change la taille du menu burger
        menuResponsiveBtn.style.height = '20px'
        menuResponsiveBtn.style.width = '20px'
        menuResponsiveBtn
            .querySelectorAll('.hamburger-menu-bar')
            .forEach(bar => bar.style.transformOrigin = '15px')
    } else {
        // Si la barre de scroll est tout en haut

        // Change la taille du menu principal
        headerPrimary.style.height = '100px'
        headerPrimary.style.position = 'relative'

        // Change la taille du logo
        headerPrimary.querySelector('img').style.height = '80px'

        // Change la taille du menu burger
        menuResponsiveBtn.style.height = '40px'
        menuResponsiveBtn.style.width = '40px'
        menuResponsiveBtn.querySelectorAll('.hamburger-menu-bar')
            .forEach(bar => bar.style.transformOrigin = '31px')
    }
}

const burgerBtn = document.querySelector(".header .header-primary .menu-responsive-btn")
if (burgerBtn) {
    burgerBtn.addEventListener('click', (e) => {
        const target = e.currentTarget
        const menu = document.querySelector(".header .header-primary .header-titles")
        const body = document.querySelector('body')
        menu.style.top = document.querySelector(".header .header-primary").style.height

        if (menu.style.display === 'flex') {
            // Si le menu est ouvert, on le ferme

            body.style.overflow = 'auto'
            target.classList.remove('-close')
            menu.style.bottom = '100%'

            // Permet d'avoir une animation propre a la fermeture du menu
            setTimeout(function () {
                menu.querySelectorAll('.header-title').forEach(li => li.style.display = 'none')

                setTimeout(function () {
                    menu.style.display = 'none'
                }, 200); // For trigger/see css transition
            }, 200) // For trigger/see css transition
        } else {
            // Si le menu est fermé, on l'ouvre

            body.style.overflow = 'hidden'
            target.classList.add('-close')

            menu.style.display = 'flex'

            // Permet d'avoir une animation propre a l'ouverture du menu
            setTimeout(function () {
                menu.style.bottom = '0'

                setTimeout(function () {
                    menu.querySelectorAll('.header-title').forEach(li => li.style.display = 'block')
                }, 150)
            }, 10) // For trigger/see css transition
        }
    })
}

window.onresize = () => {
    const menu = document.querySelector(".header .header-primary .header-titles")

    if (menu === undefined) {
        return
    }

    // Ferme le menu en cas de redimensionnement de la page
    document.querySelector('body').style.overflow = 'auto'
    menu.style.removeProperty('display')

    menu.querySelectorAll('.header-title').forEach(li => {
        li.style.removeProperty('display')
    })

    document.querySelector(".header .header-primary .menu-responsive-btn").classList.remove('-close')
}

// Permet d'ouvrir le menu
document.querySelectorAll(".header .header-primary .header-titles .header-title").forEach((element) => {
    element.addEventListener('click', (event) => {
        event.preventDefault()

        const target = event.currentTarget

        closeHeaderElement()

        document.querySelector('#' + target.dataset.headerElement + '.header-element').classList.add('-active')
    })
})

// Ferme le menu si on clique en dehors
document.addEventListener('click', (event) => {
    const target = event.target

    if (
        !target.closest('.header-element') &&
        !target.closest('.header-primary')
    ) {
        closeHeaderElement()
    }
})

document.querySelectorAll('.header-element .back').forEach((back) => {
    back.addEventListener('click', closeHeaderElement)
})

function closeHeaderElement() {
    document.querySelectorAll('.header-element').forEach((element) => {
        element.classList.remove('-active')
    })
}
