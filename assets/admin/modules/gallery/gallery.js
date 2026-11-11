// You should import the CSS file.
import 'viewerjs/dist/viewer.css';
import Viewer from 'viewerjs';

// View a list of images.
// Note: All images within the container will be found by calling `element.querySelectorAll('img')`.

document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelector('.thumbnails-container #galleries')) {
        new Viewer(document.querySelector('.thumbnails-container #galleries'), {
            url: 'data-original-photo',
            toolbar: false,
            title: false,
            movable: false,
            zoomable: false,
        })
    }
})
