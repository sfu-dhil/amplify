function getTinyMceConfig(editorUploadPath) {

    return {
        branding: false,
        selector: '.tinymce',
        plugins: 'advlist anchor charmap code help hr link ' +
            'lists paste preview searchreplace table wordcount',
        relative_urls: false,
        convert_urls: false,
        height: 320,
        menubar: 'edit insert view format table tools help',

        toolbar: [
            'undo redo | styleselect | pastetext | bold italic | alignleft aligncenter alignright alignjustify | table',
            'bullist numlist | outdent indent | link | charmap | code'],

        browser_spellcheck: true,

        resize: true,
        paste_as_text: false,
        paste_block_drop: true,
        paste_enable_default_filters: false,
        paste_word_valid_elements: 'a,b,strong,i,em,h1,h2',

        style_formats_merge: true,
    };

}
