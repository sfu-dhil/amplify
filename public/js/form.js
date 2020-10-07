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

    function formPopup(e) {
        e.preventDefault();
        var url = $(this).prop('href');
        window.open(url, "_blank", "toolbar=no,scrollbars=yes,resizable=yes,top=60,left=60,width=500,height=600");
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
        $(element).find('input').autocomplete({
            minLength: 3,
            source: function(request, response) {oclcLookup(element,request,response);},
        }).data("ui-autocomplete")._renderItem = function(ul, item){
            return $("<li></li>").data('item.autocomplete', item).append(item.label).appendTo(ul);
        };
    }

    function simpleCollection() {
        $('.collection-simple').collection({
            init_with_n_elements: 1,
            allow_up: false,
            allow_down: false,
            max: 400,
            add: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></a>',
            remove: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></a>',
            add_at_the_end: false,
            after_add: attachOclcFast
        });
    }

    function complexCollection() {
        $('.collection-complex').collection({
            init_with_n_elements: 1,
            allow_up: false,
            allow_down: false,
            max: 400,
            add: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></a>',
            remove: '<a href="#" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></a>',
            add_at_the_end: true,
            after_add: function(collection, element){
                $(element).find('.select2entity').select2entity();
                $(element).find('.select2-container').css('width', '100%');
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

    $(document).ready(function () {
        $(window).bind('beforeunload', windowBeforeUnload);
        $('form').each(formDirty);
        $("a.popup").click(formPopup);
        $("a").each(link);
        $("*[data-confirm]").each(confirm);
        $('[data-toggle="popover"]').popover(); // add this line to enable boostrap popover
        if (typeof $().collection === 'function') {
            simpleCollection();
            complexCollection();
        }
        imageModals();
    });

})(jQuery, window);
