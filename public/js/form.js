(function ($, window) {

    var hostname = window.location.hostname.replace('www.', '');

    const fastUrl = '//fast.oclc.org/searchfast/fastsuggest';

    function confirm() {
        var $this = $(this);
        $this.click(function () {
            return window.confirm($this.data('confirm'));
        });
    }

    function link() {
        if(this.hostname.replace('www.', '') === hostname) {
            return;
        }
        $(this).attr('target', '_blank');
    }

    function windowBeforeUnload(e) {
        var clean = true;
        $('form').each(function () {
            var $form = $(this);
            if ($form.data('dirty')) {
                clean = false;
            }
        });
        if (!clean) {
            var message = 'You have unsaved changes.';
            e.returnValue = message;
            return message;
        }
    }

    function formDirty() {
        var $form = $(this);
        $form.data('dirty', false);
        $form.on('change', function () {
            $form.data('dirty', true);
        });
        $form.on('submit', function () {
            $(window).unbind('beforeunload');
        });
    }

    function oclcLookup(element, request, response) {
        let suggestIdx = 'suggestall';
        let q = $(element).find('input').val();
        let fields = ['suggestall', 'idroot', 'auth', 'type'].join(',');
        let url = `${fastUrl}?query=${q}&queryIndex=${suggestIdx}&queryReturn=${fields}&suggest=autoSubject`;
        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'jsonp',
            cache: false,

            success: function(data) {
                let subjects = [];
                let result = data.response.docs;
                result.forEach(function(d){
                    let label = `<b>${d.auth}</b>`;
                    if(d.type==='alt') {
                        label = `<i>${d.suggestall}</i> use ${label}`;
                    }
                    subjects.push({
                        label: `<span>${label}</span>`,
                        value: d.auth,
                    });
                });
                response(subjects);
            },
            error: function(jq, status, error) {
                console.log(`lookup error: ${status} - ${error}`);
                response();
            },
        });
    }

    function attachOclcFast(collection, element) {
        let $element = $(element);
        if(! $element.parent().hasClass('oclcfast')) {
            return;
        }
        $element.find('input').autocomplete({
            minLength: 3,
            source: function(request, response) {oclcLookup(element,request,response);},
        }).data("ui-autocomplete")._renderItem = function(ul, item){
            return $("<li></li>").data('item.autocomplete', item).append(item.label).appendTo(ul);
        };
    }

    function simpleCollection() {
        if ( $('.collection-simple').length == 0 ) {
            return
        }
        $('.collection-simple').collection({
            init_with_n_elements: 1,
            allow_up: false,
            allow_down: false,
            max: 400,
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i></a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: attachOclcFast
        });
    }

    function complexCollection() {
        if ( $('.collection-complex').length == 0 ) {
            return
        }
        $('.collection-complex').collection({
            init_with_n_elements: 1,
            allow_up: false,
            allow_down: false,
            max: 400,
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i></a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                if (tinymce && $(element).find('.tinymce').length > 0 ) {
                    $(element).find('.tinymce').each(function (index, textarea) {
                        tinymce.execCommand("mceAddEditor", false, textarea.id);
                    });
                }
                return true;
            },
        });
    }

    function mediaCollection() {
        if ( $('.collection-media').length == 0 ) {
            return
        }
        $('.collection-media.collection-media-image').collection({
            init_with_n_elements: 0,
            allow_up: false,
            allow_down: false,
            max: 400,
            position_field_selector: '.position-id',
            add_at_the_end: true,
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Image</a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                if (tinymce && $(element).find('.tinymce').length > 0 ) {
                    $(element).find('.tinymce').each(function (index, textarea) {
                        tinymce.execCommand("mceAddEditor", false, textarea.id);
                    });
                }
                return true;
            },
        });
        $('.collection-media.collection-media-audio').collection({
            init_with_n_elements: 0,
            allow_up: false,
            allow_down: false,
            max: 400,
            position_field_selector: '.position-id',
            add_at_the_end: true,
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Audio</a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                if (tinymce && $(element).find('.tinymce').length > 0 ) {
                    $(element).find('.tinymce').each(function (index, textarea) {
                        tinymce.execCommand("mceAddEditor", false, textarea.id);
                    });
                }
                return true;
            },
        });
        $('.collection-media.collection-media-pdf').collection({
            init_with_n_elements: 0,
            allow_up: false,
            allow_down: false,
            max: 400,
            position_field_selector: '.position-id',
            add_at_the_end: true,
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Transcript</a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                if (tinymce && $(element).find('.tinymce').length > 0 ) {
                    $(element).find('.tinymce').each(function (index, textarea) {
                        tinymce.execCommand("mceAddEditor", false, textarea.id);
                    });
                }
                return true;
            },
        });
    }


    function imageModals() {
        $('#imgModal').on('show.bs.modal', function (event) {
            var $button = $(event.relatedTarget);
            // Button that triggered the modal
            var $modal = $(this);

            $modal.find('#modalTitle').text($button.data('title'));
            $modal.find('figcaption').html($button.parent().parent().find('.caption').clone());
            $modal.find("#modalImage").attr('src', $button.data('img'));
        });
    }

    function menuTabs() {
        const localStorageId = `tab-${location.href}-target`
        const lastShownTab = localStorage.getItem(localStorageId)

        const tabToggleList = document.querySelectorAll('[data-bs-toggle="tab"]')
        tabToggleList.forEach(function (tabToggle) {
            tabToggle.addEventListener('shown.bs.tab', () => {
                localStorage.setItem(localStorageId, tabToggle.id)
            })
            if (lastShownTab === tabToggle.id) {
                new bootstrap.Tab(tabToggle).show()
            }
        });
    }

    $(document).ready(function () {
        $(window).bind('beforeunload', windowBeforeUnload);
        $('form').each(formDirty);
        $("a").each(link);
        $("*[data-confirm]").each(confirm);
        let popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                html: true,
                trigger: 'focus',
            })
        }) // add this line to enable bootstrap popover
        let alertList = document.querySelectorAll('.alert')
        alertList.forEach(function (alert) {
            new bootstrap.Alert(alert);
        }); // add alert dismissal
        if (typeof $().collection === 'function') {
            simpleCollection();
            complexCollection();
            mediaCollection();
        }
        imageModals();
        menuTabs();
        // The autocomplete widget must be manually added for existing
        // elements.
        $(".collection-simple .mb-3.row").each(function(i,e){
            attachOclcFast(null, e);
        });
    });

})(jQuery, window);
