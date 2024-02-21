function getTinyMceConfig() {

    return {
        branding: false,
        promotion: false,
        selector: '.tinymce',
        plugins: 'advlist anchor charmap code help link ' +
            'lists preview searchreplace table wordcount',
        relative_urls: false,
        convert_urls: false,
        height: 320,
        menubar: false,

        toolbar: [
            'undo redo | styles | pastetext | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | table | bullist numlist | outdent indent | link | charmap | code'
        ],

        browser_spellcheck: true,

        resize: true,
        paste_as_text: false,
        paste_block_drop: true,

        style_formats_merge: true,
        statusbar: false,
        sidebar: false,
        highlight_on_focus: true,
    };

}
