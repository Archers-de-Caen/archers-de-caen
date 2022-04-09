import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';

import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
import Strikethrough from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline';
import Subscript from '@ckeditor/ckeditor5-basic-styles/src/subscript';
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript';
import Link from '@ckeditor/ckeditor5-link/src/link';
import LinkImage from '@ckeditor/ckeditor5-link/src/linkimage';
import List from '@ckeditor/ckeditor5-list/src/list';
import TodoList from '@ckeditor/ckeditor5-list/src/todolist';
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import Heading from '@ckeditor/ckeditor5-heading/src/heading';
import Undo from '@ckeditor/ckeditor5-undo/src/undo'
import HorizontalLine from '@ckeditor/ckeditor5-horizontal-line/src/horizontalline'
import GeneralHtmlSupport from '@ckeditor/ckeditor5-html-support/src/generalhtmlsupport';
import SourceEditing from '@ckeditor/ckeditor5-source-editing/src/sourceediting';

import FileRepository from '@ckeditor/ckeditor5-upload/src/filerepository'
/* TODO: maybe un jour
import CKFinder from '@ckeditor/ckeditor5-ckfinder/src/ckfinder';
import Uploadadapter from '@ckeditor/ckeditor5-adapter-ckfinder/src/uploadadapter';
 */
import ImageUpload from '@ckeditor/ckeditor5-image/src/imageupload'
import Image from '@ckeditor/ckeditor5-image/src/image'
import ImageResize from '@ckeditor/ckeditor5-image/src/imageresize'
import ImageToolbar from '@ckeditor/ckeditor5-image/src/imagetoolbar'
import AutoImage from '@ckeditor/ckeditor5-image/src/autoimage'
import ImageInsert from '@ckeditor/ckeditor5-image/src/imageinsert'
import ImageTextAlternative from '@ckeditor/ckeditor5-image/src/imagetextalternative'
import ImageCaption from '@ckeditor/ckeditor5-image/src/imagecaption'

import Indent from '@ckeditor/ckeditor5-indent/src/indent'
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar'
import Table from '@ckeditor/ckeditor5-table/src/table'
import FontSize from '@ckeditor/ckeditor5-font/src/fontsize'
import FontColor from '@ckeditor/ckeditor5-font/src/fontcolor'
import FontBackgroundColor from '@ckeditor/ckeditor5-font/src/fontbackgroundcolor'
import HtmlEmbed from '@ckeditor/ckeditor5-html-embed/src/htmlembed'

import MyUploadAdapter from "./MyUploadAdapter";

function MyCustomUploadAdapterPlugin( editor ) {
    editor.plugins.get( FileRepository ).createUploadAdapter = ( loader ) => {
        return new MyUploadAdapter( loader );
    };
}

if (document.querySelector( '#editor' )) {
    ClassicEditor
        .create( document.querySelector( '#editor' ), {
            extraPlugins: [ MyCustomUploadAdapterPlugin ],
            plugins: [
                Alignment,
                FileRepository,
                Heading,
                Undo,
                Image,
                ImageUpload,
                ImageResize,
                ImageToolbar,
                AutoImage,
                LinkImage,
                ImageInsert,
                ImageTextAlternative,
                ImageCaption,
                /* TODO: maybe un jour CKFinder, */
                /* TODO: maybe un jour Uploadadapter, */
                Indent,
                TableToolbar,
                Bold,
                Italic,
                Strikethrough,
                Underline,
                Subscript,
                Superscript,
                Link,
                TodoList,
                FontSize,
                FontBackgroundColor,
                FontColor,
                Table,
                List,
                HorizontalLine,
                HtmlEmbed,
                GeneralHtmlSupport,
                SourceEditing
            ],
            toolbar: {
                items: [
                    'heading', '|',
                    'alignment', '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'subscript', 'superscript', '|',
                    'link', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'fontsize', 'fontColor', 'fontBackgroundColor', '|',
                    'insertTable', 'horizontalLine', '|',
                    'outdent', 'indent', '|',
                    'uploadImage', /* TODO: maybe un jour 'ckfinder', */ '|',
                    'undo', 'redo', '|',
                    'htmlEmbed', 'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            image: {
                toolbar: [ 'toggleImageCaption', 'imageTextAlternative' ]
            },
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: true,
                        classes: true,
                        styles: true
                    }
                ]
            }
            // TODO: maybe un jour
            // ckfinder: {
            //     // Upload the images to the server using the CKFinder QuickUpload command.
            //     uploadUrl: 'https://example.com/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&responseType=json',
            //     openerMethod: 'modal',
            //     options: {
            //         resourceType: 'Images'
            //     }
            // }
        } )
        .then( editor => {
            window.editor = editor;
        } )
        .catch( error => {
            console.error( 'There was a problem initializing the editor.', error );
        } );
}