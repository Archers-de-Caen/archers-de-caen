headerSize()

window.onscroll = headerSize

function headerSize() {
    const headerPrimary = document.querySelector(".header .header-primary")
    const headerSecondary = document.querySelector(".header .header-secondary")
    const menuResponsiveBtn = headerPrimary.querySelector('.menu-responsive-btn')

    headerPrimary.querySelector('ul').style.top = headerPrimary.style.height

    if (
        document.body.scrollTop > headerSecondary.clientHeight ||
        document.documentElement.scrollTop > headerSecondary.clientHeight
    ) {
        headerPrimary.style.height = '50px'
        headerPrimary.style.position = 'fixed'
        headerPrimary.style.top = '0'
        headerPrimary.style.left = '0'
        headerPrimary.style.right = '0'
        headerPrimary.querySelector('img').style.height = '25px'

        menuResponsiveBtn.style.height = '20px'
        menuResponsiveBtn.style.width = '20px'
        menuResponsiveBtn.querySelectorAll('.hamburger-menu-bar')
            .forEach(bar => bar.style.transformOrigin = '15px')
    } else {
        headerPrimary.style.height = '100px'
        headerPrimary.style.position = 'relative'
        headerPrimary.querySelector('img').style.height = '50px'

        menuResponsiveBtn.style.height = '40px'
        menuResponsiveBtn.style.width = '40px'
        menuResponsiveBtn.querySelectorAll('.hamburger-menu-bar')
            .forEach(bar => bar.style.transformOrigin = '31px')
    }
}

document.querySelector(".header .header-primary .menu-responsive-btn")
    .addEventListener('click', (e) => {
        const target = e.currentTarget
        const menu = document.querySelector(".header .header-primary ul")
        const body = document.querySelector('body')
        menu.style.top = document.querySelector(".header .header-primary").style.height

        if (menu.style.display === 'flex') {
            body.style.overflow = 'auto'
            target.classList.remove('-close')

            menu.style.bottom = '100%'
            setTimeout(function () {
                menu.querySelectorAll('li').forEach(li => li.style.display = 'none')

                setTimeout(function () {menu.style.display = 'none'}, 200); // For trigger/see css transition
            }, 200) // For trigger/see css transition
        } else {
            body.style.overflow = 'hidden'
            target.classList.add('-close')

            menu.style.display = 'flex'

            setTimeout(function () {
                menu.style.bottom = '0'

                setTimeout(function () {
                    menu.querySelectorAll('li').forEach(li => li.style.display = 'block')
                }, 150)
            }, 10) // For trigger/see css transition
        }
    })

window.onresize = () => {
    const menu = document.querySelector(".header .header-primary ul")
    const menuStyle = menu.style
    document.querySelector('body').style.overflow = 'auto'
    menuStyle.removeProperty('display')
    menu.querySelectorAll('li').forEach(li => {
        const liStyle = li.style
        liStyle.removeProperty('display')
    })
    document.querySelector(".header .header-primary .menu-responsive-btn").classList.remove('-close')
}