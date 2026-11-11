import './modules/header/header'
import '../admin/modules/gallery/gallery'
import './modules/carousels/carousels'
import './modules/toggleable/toggleable'

import './js/share-btn'
import './js/copy'
import './js/flash'
import './js/iframe-fit-content'
import './js/display'
import './js/embedly'
import './js/open-native-share'

import Swal from 'sweetalert2'
window.Swal = Swal

// feather-icons a plugin for the open-source icon set, show https://feathericons.com/ for complete icons list
import feather from 'feather-icons'
window.onload = function () {
    feather.replace()
}

import '@fortawesome/fontawesome-free/js/all.js'
