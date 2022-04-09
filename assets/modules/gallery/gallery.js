// You should import the CSS file.
import 'viewerjs/dist/viewer.css';
import Viewer from 'viewerjs';

// View a list of images.
// Note: All images within the container will be found by calling `element.querySelectorAll('img')`.

window.onload = function () {
    new Viewer(document.getElementById('galleries'))
}



// Then, show one image by click it, or call `gallery.show()`.