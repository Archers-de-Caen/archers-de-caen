/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

import './modules/header/header'
import './modules/gallery/gallery'
import './modules/carousels/carousels'
import './modules/toggleable/toggleable'

import './js/share-btn'
import './js/copy'
import './js/flash'
import './js/iframe-fit-content'
import './js/display'

// feather-icons a plugin for the open-source icon set, show https://feathericons.com/ for complete icons list
const feather = require('feather-icons')
window.onload = function () {
    feather.replace()
}

// start the Stimulus application
// import './bootstrap';

require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');
