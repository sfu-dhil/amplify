(function ($, window) {

    var hostname = window.location.hostname.replace('www.', '');

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

    function complexCollection() {
        if ( $('.collection-complex').length == 0 ) {
            return
        }
        $('.collection-complex').each( (index, collectionEl) => {
            const label = $(collectionEl).data('collection-label');
            $(collectionEl).collection({
                init_with_n_elements: 0,
                allow_up: false,
                allow_down: false,
                max: 400,
                add_at_the_end: true,
                add: `<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add New ${label}</a>`,
                remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
                after_add: function(collection, element){
                    $(element).find('.select2entity').select2entity();
                    $(element).find('.select2-simple').select2({
                        width: '100%',
                    });
                    if (tinymce && $(element).find('.tinymce').length > 0 ) {
                        $(element).find('.tinymce').each(function (index, textarea) {
                            tinymce.execCommand("mceAddEditor", false, textarea.id);
                        });
                    }
                    return true;
                },
            })
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
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add New Image</a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                $(element).find('.select2-simple').select2({
                    width: '100%',
                });
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
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add New Audio</a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                $(element).find('.select2-simple').select2({
                    width: '100%',
                });
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
            add: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add New Transcript</a>',
            remove: '<a href="#" class="btn btn-primary btn-sm"><i class="bi bi-dash-circle"></i></a>',
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                $(element).find('.select2-simple').select2({
                    width: '100%',
                });
                if (tinymce && $(element).find('.tinymce').length > 0 ) {
                    $(element).find('.tinymce').each(function (index, textarea) {
                        tinymce.execCommand("mceAddEditor", false, textarea.id);
                    });
                }
                return true;
            },
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
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        if (typeof $().collection === 'function') {
            complexCollection();
            mediaCollection();
        }
        menuTabs();
        $('.select2-simple').select2({
            width: '100%',
        });

        const select2entityModal = document.getElementById('select2entity_modal')
        if (select2entityModal) {
            select2entityModal.addEventListener('show.bs.modal', event => {
                console.log('event', event);
                const button = event.relatedTarget
                const contentRoute = button.getAttribute('data-modal-content-route');
                $(select2entityModal).find('.modal-content').load(contentRoute, () => {
                    // form submission
                    $(select2entityModal).find('form').submit((formSubmitEvent) => {
                        formSubmitEvent.preventDefault();
                        console.log($(select2entityModal).find('form').attr("action"));
                        console.log($(select2entityModal).find('form').serialize());
                        $.post(
                            $(select2entityModal).find('form').attr("action"),
                            $(select2entityModal).find('form').serialize(),
                            (data) => {
                                if (data.success) {
                                    $(select2entityModal).modal('hide');
                                } else {
                                    alert('There was a problem saving the form.');
                                }
                            },
                        );
                    });
                    // initialize select2entity, select2 simple, and tinymce for modal content
                    $(select2entityModal).find('.select2entity').select2entity();
                    $(select2entityModal).find('.select2-simple').select2({
                        width: '100%',
                    });
                    if (tinymce && $(select2entityModal).find('.tinymce').length > 0 ) {
                        $(select2entityModal).find('.tinymce').each(function (index, textarea) {
                            tinymce.execCommand('mceRemoveEditor', false, textarea.id);
                            tinymce.execCommand("mceAddEditor", false, textarea.id);
                        });
                    }
                });
            });
            select2entityModal.addEventListener('hidden.bs.modal', () => {
                // remove current modal content on hide
                $(select2entityModal).find('.modal-content').html('');
            });
        }

        let showMoreContentList = [].slice.call(document.querySelectorAll('.show-more-content'))
        showMoreContentList.map((showMoreContentEl) => {
            if (showMoreContentEl.offsetHeight < showMoreContentEl.scrollHeight || showMoreContentEl.offsetWidth < showMoreContentEl.scrollWidth) {
                const showMoreButtonEl = $('<button class="btn btn-primary btn-sm mb-3">Show more</button>')
                const showMore = () => {
                    $(showMoreContentEl).toggleClass('show-more-content')
                    $(showMoreContentEl).off('click')
                    $(showMoreButtonEl).remove()
                }
                $(showMoreContentEl).click(showMore)
                $(showMoreButtonEl).click(showMore)
                $(showMoreContentEl).after(showMoreButtonEl)
            } else {
                $(showMoreContentEl).toggleClass('show-more-content')
            }
        })
        if (location.hash) {
            let scrollOffset = document.querySelector(location.hash).offsetTop
            const setAnchorScroll = () => {
                $('.page-content').scrollTop(scrollOffset - 20);
            }
            const updateAnchorScroll = () => {
                const currentScrollOffset = document.querySelector(location.hash).offsetTop
                if (scrollOffset != currentScrollOffset) {
                    scrollOffset = currentScrollOffset
                    setAnchorScroll()
                }
            }
            setAnchorScroll()
            setTimeout(updateAnchorScroll, 1000)
            setTimeout(updateAnchorScroll, 2000)
            setTimeout(updateAnchorScroll, 3000)
            setTimeout(updateAnchorScroll, 4000)
        }

        // fix collection required label
        $('legend.required, label.required').each( (index, element) => {
            if ($(element).next().hasClass('collection')) {
                $(element).removeClass('required');
            }
        });

        // fix select2 not focusing properly on open https://stackoverflow.com/a/67691578
        $(document).on('select2:open', (e) => {
            $(`.select2-search__field[aria-controls='select2-${e.target.id}-results']`).each((key, value) => {
                value.focus();
            });
        })
    });

})(jQuery, window);
