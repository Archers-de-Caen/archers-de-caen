// You should import the CSS file.
import 'viewerjs/dist/viewer.css';
import Viewer from 'viewerjs';

// View a list of images.
// Note: All images within the container will be found by calling `element.querySelectorAll('img')`.

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('galleries')) {
        new Viewer(document.getElementById('galleries'), {
            url(image) {
                return image.getAttribute('data-original-photo')
            },
        })
    }
})



// Then, show one image by click it, or call `gallery.show()`.
