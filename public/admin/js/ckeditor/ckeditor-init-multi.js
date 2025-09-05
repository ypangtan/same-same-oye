function UploadAdapterPlugin( editor ) {
    editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
        return new UploadAdapter( loader, window.ckeupload_path, window.csrf_token );
    };
}

const editorIds = Array.isArray( window.cke_element ) ? window.cke_element : ( typeof window.cke_element === 'string' ? [window.cke_element] : [] );

window.editors = {};

editorIds.forEach(id => {
    if (id) {
        ClassicEditor
        .create( document.getElementById( id ), {
            licenseKey: '',
            extraPlugins: [ UploadAdapterPlugin ],
        } )
        .then( editor => {
            window.editors[id] = editor;
        } )
        .catch( error => {
            console.error( 'Oops, something went wrong!' );
            console.error( 'Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:' );
            console.warn( 'Build id: nhwyd6r0s6k-qyh8t72ssh4f' );
            console.error( error );
        } );
    }
});