/**
 * @deprecated It is safe to delete this file
 *
 */
$(function () {

    if (typeof sayso === 'undefined') sayso = {};

    // ==============================================================
    // functions/helpers

    if (!Array.prototype.indexOf)
    {
        Array.prototype.indexOf = function(obj, start) {
            for (var i = (start || 0), j = this.length; i < j; i++) {
                if (this[i] == obj) { return i; }
            }
            return -1;
       };
    }

    /**
     * Get the data container of a given DOM object
     * - a "data container" (per this convention) is any DOM object
     *   with the HTML5 data attribute "data-id", indicating
     *   an identifiable record, within which the current object
     *   is a child
     * - default is to return the *first* parent container (index 0)
     *   but can be specified via parentIndex param (e.g. *second* (outer) parent == 1)
     * - you can also access the data container even if the current element
     *   IS the data container (i.e. it contains the "data-id" attr)
     *
     * - example: $('a.facebook').dataContainer().find('.button').show()
     * - example: $('a.facebook').dataContainer(1).getId() <-- get the id of an outer container
     * - example:
     *     <div class="reward" data-id="<?= $reward->getId() ?>">
     *         <a>Redeem!</a>
     *     </div>
     *     <script type="text/javascript">
     *         $('div.reward a').click(function () {
     *             var rewardId = $(this).dataContainer().getId();
     *             // redeem this reward
     *         });
     *     </script>
     *
     * @author davidbjames
     *
     * @param integer parentIndex OPTIONAL defaults to 0 (first parent)
     * @return jQuery object of the parent element
     */
    $.fn.dataContainer = function (parentIndex) {

        var _container;
        if (typeof parentIndex === 'number') {
            // if parent is explicitly set
            _container = this.parents('[data-id]').eq(parentIndex);
        } else if (typeof this.attr('data-id') !== 'undefined') {
            // if the data-id exists on *this* element
            _container = this;
        } else {
            // otherwise default to first parent
            _container = this.parents('[data-id]').eq(0);
        }

        if (!_container.length) {
            // if none found provide harmless object
            return {
                attr : function () { return null; },
                getId : function () { return 0; },
                setObject : function () { return this; },
                getObject : function () { return this; },
                reset : function () {},
                removeNow : function () {}
            };
        }

        // store off the id
        var _id = _container.attr('data-id');

        /**
         * Get the ID of the object
         * - this usually corresponds to the record ID
         * @returns integer
         */
        _container.getId = function () {
            return typeof _id === 'undefined' ? 0 : parseInt(_id);
        };

        /**
         * Attach an object to this data container
         * @param object|string object
         */
        _container.setObject = function (object) {
            _container.data('object', typeof(object) === 'string' ? object : JSON.stringify(object));
            return _container;
        };

        /**
         * Get the object from this data container
         * @returns object
         */
        _container.getObject = function () {
            return JSON.parse(_container.data('object'));
        };

        /**
         * Copy the current data container to another DOM node
         * @param target
         * @returns object "data container"
         */
        _container.copy = function (target) {
            if (typeof _container.data('object') !== 'undefined') {
                target.data('object', _container.data('object'));
            }
            target.attr('data-id', _container.getId());
            return target.dataContainer(); // the new data container
        };

        /**
         * Move the current data container to another DOM node
         * @param target
         * @returns object "data container"
         */
        _container.move = function (target) {
            var newContainer = _container.copy(target);
            _container.reset();
            return newContainer;
        };

        /**
         * Reset the data container (remove id and object)
         */
        _container.reset = function () {
            _container.removeAttr('data-id');
            _container.removeData('object');
            return _container;
        };

        /**
         * Remove the data container completely
         */
        _container.removeNow = function () {
            _container.fadeTo(400, 0, function() {
                _container.remove();
            });
        };

        return _container;
    };

    $.fn.makeDataContainer = function (id) {
        $(this).attr('data-id', id);
    };

    function tpl ( tpl, obj, extra ) {
        if (!extra) extra = {};
        var data = $.extend({}, obj, extra);
        return Mustache.to_html( sayso.templates[tpl], data );
    }

    function uniqueId () {
        if (typeof uniqueId.ids === 'undefined') {
            uniqueId.ids = [];
        }
        var id = (Math.floor(Math.random() * 100000) + 100);
        if (uniqueId.ids.indexOf(id) === -1) {
            uniqueId.ids.push(id);
            return id;
        } else {
            return uniqueId();
        }
    }

    function isEmpty (object) {
        for (var key in object) {
            return false;
        }
        return true;
    }

    // ==============================================================
    // sayso data + localstorage

    function resetData () {

        sayso.data = {
            type : 'ADgregator', // or 'ADjuster'
            tagdomain : {},
            domainAvail : {},
            creative : {},
            basic : {
                name: '',
                id: '',
                size: '',
                minimum: '',
                begindate: '',
                enddate: '',
                issurvey: 'no'
            },
            metrics : {
                clicktrack : 'No',
                searchengines : {},
                searchengineIds : [],
                social : {},
                socialIds : []
            },
            surveyinfo : {
                type : 'No Survey',
                tag : '',
                deliverIf : {}
            },
            quota : {},
            cells : {}
        };

        // temporary object for storing list data
        // prior to aggregation into sayso.data
        sayso.temp = {
            qualifier : {
                browse : {},
                search : {}
            },
            domain : {}
        };
    }

    // initialize the data object
    resetData();

    // on page load reset local storage
    localStorage.clear();

    // if data exists in localStorage, restore
    // NOTE: currently doesn't apply
//    if (localStorage.getItem('sayso')) {
//        sayso.data = JSON.parse(localStorage.getItem('sayso'));
//    }

    // setup change tracking to warn if user attempts to
    // switch products with unsaved changes
    var _changesPending = false;
    $('input, select').not('[type=submit]').change(function (e) {
        if ($(this).val().length) _changesPending = true;
    })

    function resetForm () {
        // reset the form
        $('input[type=checkbox],input[type=radio]').removeAttr('checked');
        $('input[type=text],textarea,input.input-date').val('');
        $('select').val(false);
        // remove any row data displayed in the dom
        $('ul.data-list').empty();
        $('table.cell-lists tbody').empty();
        $('#fieldset-cell-adtags div.radios-labeled').empty();
        $('#fieldset-creative-adtags div.radios-labeled').empty();
        $('#fieldset-creative-adtags div.radios-labeled').text('None created').addClass('notation');
        // reset flag, nothing pending (therefore allow navigating away)
        _changesPending = false;
    }

    // ==============================================================
    // UI Controls

    // tabs
    $('nav.main a').click(function(e){

        if($(this).hasClass('return-true'))
        {
            return true;
        }

        e.preventDefault();
        var _this = $(this),
            span = _this.find('span');

        function _updateLinkStyle () {
            _this.closest('ul')
                .find('a').css('color','#444444')
                .find('span').css('text-decoration','none');
            _this.css('color', 'blue');
            span.css('text-decoration', 'underline');
        }

        if (!_changesPending || confirm('There are unsaved changes. Continue anyways?')) {

            resetForm();
            resetData();

            var index = 0;
            sayso.data.type = span.text();
            switch (span.text()) {
                case 'ADjuster Campaign' :
                    index = 0;
                    $('#ad-tags').fadeIn();
                    $('#domains-creative').fadeOut();
                    _updateLinkStyle();
                    break;
                case 'ADjuster Creative' :
                    index = 1;
                    $('#domains-creative').fadeIn();
                    $('#ad-tags').fadeOut();
                    _updateLinkStyle();
                    break;
                case 'ADjuster Behavioral' :
                    index = 2;
                    $('#ad-tags').fadeOut();
                    $('#domains-creative').fadeOut();
                    _updateLinkStyle();
                    break;
            }
            // replace any product-specific text
            $('*[data-text-replace]').each(function () {
                $(this).text(JSON.parse($(this).attr('data-text-replace'))[index]);
            });
        }

    });

    // lock/min/max
    $('nav.lock').delegate( 'a.minimize, a.maximize', 'click', function(e) {

        e.preventDefault();
        var section = $(this).closest('section.main-criteria'),
            type = ( $(this).hasClass( 'minimize' )) ? "minimize" : "maximize";

        section.find('header').animate({
          marginBottom : ( type === "minimize" ) ? "0" : "15px"
        });

        if( type === "minimize" ) {
            $(this).text('+')
               .removeClass('minimize')
               .addClass('maximize');
            section.find('.section-container').slideUp();
        } else {
            $(this).text('-')
               .removeClass('maximize')
               .addClass('minimize');
            section.find('.section-container').slideDown();
        }
      });

    $('nav.lock .lock').click(function(e) {
        e.preventDefault();
    });

    // =============================================================================
    // Ad Tags (ADgregator) (NOTE: first two functions also handle ADjuster domains)

    $('button.add-domain').click(function(e) {
        e.preventDefault();
        // since this is the *first* button in the "form", this is the
        // one that is triggered if the user hits enter/return in any
        // text field, and since we don't want the form submitted
        // with enter/return, the only way to prevent it is to ensure
        // a mouse click w/actual coords is performed for this button
        if (!e.clientX && !e.clientY) return;
        if ($(this).parents('#fieldset-tag-domain-pair').length) {
            // ADgregator
            var domain = $('#pairs-domains');
        } else {
            // ADjuster
            var domain = $('#domains-avails-domain');
        }
        if (!domain.val().length) {
            alert('Please enter a domain name');
            return;
        }
        var data = {
            name : domain.val(),
            id : uniqueId()
        };
        sayso.temp.domain[data.id] = data;
        $(tpl('domains', data)).appendTo($(this).next()); // next s/b the <ul> with the domain list
        domain.val('');
    });

    $('ul.list-domains a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        delete sayso.temp.domain[id];
        container.removeNow();
    });

    $('button.add-pair').click(function(e){
        e.preventDefault();
        if (e.keyCode == '13') return;
        var label = $('#pairs-label');
        var adtag = $('#pairs-ad-tag');
        if (!label.val().length || !adtag.val().length || isEmpty(sayso.temp.domain)) {
            alert('Ad tags require a label, ad tag content and at least one domain');
            return;
        }
        var data = {
            label : label.val(),
            tag : adtag.val(),
            domain : sayso.temp.domain
        };
        sayso.temp.domain = {};
        var container = $(this).dataContainer();
        // are we editing?
        if (container.getId()) {
            // grab and remove the id
            data.id = container.getId();
            container.reset();
            // replace the list item
            $('#list-tag-domain-pair li[data-id=' + data.id + ']').replaceWith(tpl('tagDomainPairs', data));
            // also replace the ad tags listed below in cells
            $('#fieldset-cell-adtags div.radios-labeled div[data-id=' + data.id + ']').replaceWith(tpl('adTag', data));
        // or creating a new one?
        } else {
            // generate a unique id
            data.id = uniqueId();
            // display list item
            $(tpl('tagDomainPairs', data)).appendTo('#list-tag-domain-pair');
            // add this tag to the cell form below to allow adding tags to cells
            $('#fieldset-cell-adtags div.radios-labeled').append(tpl('adTag', data));
        }
        $('button.add-pair').text('Add Tag');
        // add it to/replace it in the data store (by id)
        sayso.data.tagdomain[data.id] = data;
        // reset the fields
        label.val('');
        adtag.val('');
        $('ul.list-domains li').remove();
    });

    $('#list-tag-domain-pair a.edit').live('click', function(e){
        e.preventDefault();
        var id = $(this).dataContainer().getId(),
            tagdomain = sayso.data.tagdomain[id];
        // set the data container id
        $('#tag-domain-pairs').attr('data-id', id);
        // populate the fields
        $('#pairs-label').val(tagdomain.label);
        $('#pairs-ad-tag').val(tagdomain.tag);
        if (!isEmpty(tagdomain.domain)) {
            // empty the list, just in case the user was already in the middle of editing another row
            $('#fieldset-tag-domain-pair ul.list-domains li').remove();
            for (var i in tagdomain.domain) {
                sayso.temp.domain[i] = tagdomain.domain[i];
                $(tpl('domains', tagdomain.domain[i])).appendTo('#fieldset-tag-domain-pair ul.list-domains');
            }
        }
        $('button.add-pair').text('Update');
    });

    $('#list-tag-domain-pair a.delete').live('click', function(e){
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete ad tag "' + container.find('span').text() + '"?')) {
            delete sayso.data.tagdomain[id];
            container.removeNow();
            // remove the ad tag listed below
            $('#fieldset-cell-adtags div.radios-labeled div[data-id=' + id + ']').remove();
        }
    });

    // ==============================================================
    // Domains & Creative (ADjuster)

    $('#domains-avails button.add-tag-domain-pair').click(function (e) {
        e.preventDefault();
        if (!e.clientX && !e.clientY) return; // see above for why
        var label = $('#domains-avails-label'),
            tag = $('#domains-avails-tag');

        if (!label.val().length || !tag.val().length || isEmpty(sayso.temp.domain)) {
            alert('Requires a label, ad tag content and at least one domain');
            return;
        }
        var data = {
            label : label.val(),
            tag : tag.val(),
            domain : sayso.temp.domain
        };
        sayso.temp.domain = {};
        var container = $(this).dataContainer();
        if (container.getId()) { // edit existing
            data.id = container.getId();
            container.reset();
            // replace the list item
            $('#list-domains-avails li[data-id=' + data.id +']').replaceWith(tpl('tagDomainPairs', data, { label : data.label }));
            // also replace the ad tags listed below in cells
            $('#fieldset-cell-adtags div.radios-labeled div[data-id=' + data.id + ']').replaceWith(tpl('adTag', data, { label : data.label}));
            $('#fieldset-creative-adtags div.radios-labeled div[data-id=' + data.id + ']').replaceWith(tpl('adTag', data, { label : data.label}));
        } else { // create new
            data.id = uniqueId();
            // display list item
            $(tpl('tagDomainPairs', data, { label : data.label })).appendTo('#list-domains-avails');
            // add this tag to the cell form below to allow adding tags to cells
            $('#fieldset-cell-adtags div.radios-labeled').append(tpl('adTag', data, { label : data.label}));
            var creativeAdtags = $('#fieldset-creative-adtags div.radios-labeled');
            if (!creativeAdtags.children().length) {
                // empty list, make sure placeholder text is removed
                creativeAdtags.text('');
                creativeAdtags.removeClass('notation');
            }
            creativeAdtags.append(tpl('adTag', data, { label : data.label}));
        }
        $(this).text('Add Avail');
        sayso.data.domainAvail[data.id] = data;
        label.val(''); tag.val('');
        $('ul.list-domains li').remove();
    });

    $('#list-domains-avails a.edit').live('click', function(e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId(),
            domainAvail = sayso.data.domainAvail[id];
        $('#domains-avails').makeDataContainer(id);
        $('#domains-avails-label').val(domainAvail.label);
        $('#domains-avails-tag').val(domainAvail.tag);
        $('#domains-avails button.add-tag-domain-pair').text('Update');
        if (!isEmpty(domainAvail.domain)) {
            // empty list, just in case the user was already in the middle of editing another row
            $('#fieldset-domains-avails ul.list-domains li').remove();
            for (var i in domainAvail.domain) {
                sayso.temp.domain[i] = domainAvail.domain[i];
                $(tpl('domains', domainAvail.domain[i])).appendTo('#fieldset-domains-avails ul.list-domains');
            }
        }
    });

    $('#list-domains-avails a.delete').live('click', function (e){
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete domain/avail for "' + container.find('span').text() + '"?')) {
            delete sayso.data.domainAvail[id];
            container.removeNow();
            // remove the ad tag listed below
            $('#fieldset-cell-adtags div.radios-labeled div[data-id=' + id + ']').remove();
            $('#fieldset-creative-adtags div.radios-labeled div[data-id=' + id + ']').remove();
            if (!$('#fieldset-creative-adtags div.radios-labeled').children().length) {
                $('#fieldset-creative-adtags div.radios-labeled').addClass('notation').text('None created');
            }
            // remove any references to domains/avails from any creatives
            var warn = 0;
            for (var i in sayso.data.creative) {
                if (!isEmpty(sayso.data.creative[i].domainAvail)) {
                    for (var j in sayso.data.creative[i].domainAvail) {
                        if (sayso.data.creative[i].domainAvail[j] === id) {
                            sayso.data.creative[i].domainAvail.splice(j, 1);
                        }
                    }
                    if (!sayso.data.creative[i].domainAvail.length) {
                        warn++;
                    }
                }
            }
            if (warn) {
                alert('Deleting this avail has invalidated ' + warn + ' of the creatives -- i.e. no avail(s) associated. Please review and fix your creatives.');
            }

        }
    });

    $('#creative button.add-creative').click(function (e) {
        e.preventDefault();
        var name = $('#creative-name'),
            creativeUrl = $('#creative-url'),
            contentType = $('#creative-type'),
            //file = $('#creative-file'),
            targetUrl = $('#creative-target-url');
        if (!name.val().length || !creativeUrl.val().length || !targetUrl.val().length) {
            alert('Name, creative URL and target URL are required');
            return;
        }
        // gather up domains/avails that are associated with this creative
        var domainAvail = [];
        $('#fieldset-creative-adtags input[type=checkbox]:checked').each(function () {
            var id = $(this).dataContainer().getId();
            // if domain/avail is checked, then add it to the list for this creative
            domainAvail.push(id);
        });
        if (!domainAvail.length) {
            alert('Please select at least one avail for this creative.');
            return;
        }
        var data = {
            name : name.val(),
            creativeUrl : creativeUrl.val(),
            contentType : contentType.val(),
            targetUrl : targetUrl.val(),
            domainAvail : domainAvail
        };
        var container = $(this).dataContainer();
        if (container.getId()) { // edit existing
            data.id = container.getId();
            container.reset(); // to indicate we are going OUT of editing mode
            $('#list-creative li[data-id=' + data.id + ']').replaceWith(tpl('creative', data));
        } else { // create new
            data.id = uniqueId();
            $(tpl('creative', data)).appendTo('#list-creative');
        }
        $(this).text('Add Creative');
        sayso.data.creative[data.id] = data;
        name.val(''); contentType.val(false); creativeUrl.val(''); targetUrl.val('');
        $('#fieldset-creative-adtags input[type=checkbox]').removeAttr('checked');
    });

    $('#list-creative a.edit').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId(),
            creative = sayso.data.creative[id];
        $('#creative').makeDataContainer(id);
        $('#creative-name').val(creative.name);
        $('#creative-url').val(creative.creativeUrl);
        $('#creative-target-url').val(creative.targetUrl);
        $('#creative button.add-creative').text('Update');
        for (var i in creative.domainAvail) {
            $('#fieldset-creative-adtags div[data-id=' + creative.domainAvail[i] + '] input').attr('checked', true);
        }
    });

    $('#list-creative a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete creative for "' + container.find('span.creative-name').text() + '"?')) {
            delete sayso.data.creative[id];
            container.removeNow();
        }
    });

    // no target URL for certain types (such as HTML)
    $('#creative-type').live('change', function (e) {
        if ($(this).val() == 8) {
            $('#creative-target-url').attr('disabled', 'disabled');
        } else {
            $('#creative-target-url').removeAttr('disabled');
        }
    });

    // ==============================================================
    // Survey

    $('input[name=type-survey]').change(function(){
        if ($(this).val() === 'Custom Survey') {
            $('label.paste-iframe-label, input#type-iframe').fadeIn('slow');
        } else {
            $('label.paste-iframe-label, input#type-iframe').fadeOut('slow');
        }
    });

    $('#add-delivery-criteria').click(function(e) {
        e.preventDefault();
        if (!$('#type-survey-2, #type-survey-3').is(':checked')) {
            alert('Please select a Survey Type first');
            return;
        }

        var domain = $('#delivery-domain'),
            timeframe = $('#delivery-timeframe');

        // if no domain in the text field, look in the history drop down
        if (!domain.val().length) domain = $('#delivery-domain-history');

        if (!domain.val().length || !timeframe.val().length) {
            alert('Delivery Criteria requires a domain and a time frame');
            return;
        }
        var data = {
            domain : domain.val(),
            timeframe : timeframe.find(':checked').text(),
            timeframeId : timeframe.val()
        };
        data.id = uniqueId();
        sayso.data.surveyinfo.deliverIf[data.id] = data;
        $(tpl('deliveryCriteria', data)).appendTo('#list-survey-delivery-criteria');
        // reset domain
        $('#delivery-domain').val('');
        $('#delivery-domain-history').val(false);
        timeframe.val(false);
    });

    $('#list-survey-delivery-criteria a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        delete sayso.data.surveyinfo.deliverIf[id];
        container.removeNow();
    });

    // ==============================================================
    // Ad Effectiveness

    $('input.input-date').datepicker();

    // ==============================================================
    // Quotas

    $('#add-quota').click(function(e) {
        e.preventDefault();
        var gender = $('#study-gender'),
            age = $('#study-age'),
            percent = $('#study-size-percent'),
            ethnicity = $('#study-ethnicity');
        if (!gender.val() && !age.val() && !ethnicity.val()) {
            alert('Quotas must have at least one criteria (gender, age or ethnicity)');
            return;
        }
        var data = {
            gender : gender.find(':checked').text(),
            genderId : gender.val(),
            age : age.find(':checked').text(),
            ageId : age.val(),
            ethnicity : ethnicity.find(':checked').text(),
            ethnicityId : ethnicity.val(),
            percent : percent.find(':checked').text(),
            percentId : percent.val()
            // this may need a prop of 'type'
        };
        data.id = uniqueId();
        sayso.data.quota[data.id] = data;
        // validate quota percent
        var checkPercent = 0;
        for (var i in sayso.data.quota) {
            checkPercent += parseInt($('#study-size-percent option[value=' + sayso.data.quota[i].percentId + ']').attr('data-percent'));
        }
        if (checkPercent > 100) {
            alert('Total quotas exceeds 100%');
            delete sayso.data.quota[data.id];
            return;
        }
        // @todo handle missing data required by templates
        $(tpl('quotas', data)).appendTo('#list-study-quota');
        gender.val(false); age.val(false); ethnicity.val(false); percent.val(false);
    });

    $('#list-study-quota a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete the quota "' + container.find('span').text() + '"?')) {
            delete sayso.data.quota[id];
            container.removeNow();
        }
    });

    // ==============================================================
    // Cells

    // online browsing
    $('#add-browsing-qualifier').click(function(e) {
        e.preventDefault();
        var includeExclude = $('#browsing-include-exclude'),
            domainName = $('#browsing-domain-name'),
            timeframe = $('#browsing-timeframe');
        if (!includeExclude.val()) {
            alert('Browsing qualifier must either include OR exclude panelists');
            return;
        }
        if (!domainName.val() && !timeframe.val()) {
            alert('Browsing qualifier must have at least one criteria (domain name or time frame)');
            return;
        }
        var data = {
            include : includeExclude.val(),
            site : domainName.val(),
            timeframe : timeframe.find(':checked').text(),
            timeframeId : timeframe.val()
        };
        data.id = uniqueId();
        sayso.temp.qualifier.browse[data.id] = data;
        // @todo handle missing data required by templates
        $(tpl('browsingQualifiers', data)).appendTo('#list-browsing-qualifier');
        // reset fields
        includeExclude.val(false); domainName.val(''); timeframe.val(false);
    });

    $('#list-browsing-qualifier a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete the browsing qualifier "' + container.find('span').text() + '"?')) {
            delete sayso.temp.qualifier.browse[id];
            container.removeNow();
        }
    });

    // search actions
    $('#add-search-qualifier').click(function(e) {
        e.preventDefault();
        var includeExclude = $('#engine-include-exclude'),
            domainName = $('#engine-domain-name'), // this is actually a search *term*
            timeframe = $('#engine-timeframe'),
            searchEngines = $('#fieldset-search-qualifier input[type=checkbox]');
        if (!includeExclude.val()) {
            alert('Search qualifier must either include OR exclude panelists');
            return;
        }
        if (!domainName.val() && !timeframe.val() && !searchEngines.is(':checked')) {
            alert('Search qualifier must have at least one criteria (search term, time frame or search engines)');
            return;
        }

        var data = {
            include : includeExclude.val(),
            term : domainName.val(),
            timeframe : timeframe.find(':checked').text(),
            timeframeId : timeframe.val(),
            which : {
                bing : $('input.select-search-engines[data-short-name=bing]').is(':checked') ? 'Yes' : 'No',
                google : $('input.select-search-engines[data-short-name=google]').is(':checked') ? 'Yes' : 'No',
                yahoo : $('input.select-search-engines[data-short-name=yahoo]').is(':checked') ? 'Yes' : 'No'
            }, // @todo test if this is working correctly (with the template)
            whichIds : []
        };
        // add the search engine ids to an array
        $('input.select-search-engines:checked').each(function () { data.whichIds.push($(this).val()); })

        data.id = uniqueId();
        sayso.temp.qualifier.search[data.id] = data;
        // @todo handle missing data required by templates
        $(tpl('searchQualifiers', data)).appendTo('#list-search-qualifier');
        // reset fields
        includeExclude.val(false); domainName.val(''); timeframe.val(false); searchEngines.removeAttr('checked');
    });

    $('#list-search-qualifier a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete the search qualifier "' + container.find('span').text() + '"?')) {
            delete sayso.temp.qualifier.search[id];
            container.removeNow();
        }
    });

    // cells
    $('button.build-cell').click(function(e) {
        e.preventDefault();

        if (!$('#cell-description').val()) {
            alert('Study cell must have a description');
            return;
        }
        if (isEmpty(sayso.temp.qualifier.browse) && isEmpty(sayso.temp.qualifier.search)) {
            alert('Study cell must have qualifier(s)');
            return;
        }
        // validate cell size does not exceed study sample size
        if ($('#study-sample-size').val().length && $('#cell-size').val().length) {
            var cellSize = 0;
            for (var cellId in sayso.data.cells) {
                cellSize += parseInt(sayso.data.cells[cellId].size);
                console.log(cellId + ' - ' + parseInt(sayso.data.cells[cellId].size));
            }
            console.log('current: ' + parseInt($('#cell-size').val()));
            cellSize += parseInt($('#cell-size').val());
            console.log('total: ' + cellSize);
            if (cellSize > parseInt($('#study-sample-size').val())) {
                alert('Total of all cell sizes (' + cellSize + ') must not exceed Sample Size (' + $('#study-sample-size').val() + ')');
                return;
            }
        }
        // gather up any ad tags the user wants to attach to this cell
        var adtags = [];
        $('#fieldset-cell-adtags input[type=checkbox]').each(function() {
            var id = $(this).dataContainer().getId();
            if ($(this).is(':checked')) {
                adtags.push(id);
            } else if (adtags.indexOf(id) > -1) {
                adtags.splice(adtags.indexOf(id), 1);
            }
        });
        // build out the data for the cell
        var cell = {
            description : $('#cell-description').val(),
            // type defaults to the second radio, which is currently 'Test'
            type : $('input[name=cell-type]:checked').length ? $('input[name=cell-type]:checked').val() : $('#cell-type-2').val(),
            size : $('#cell-size').val(),
            deliverIf : $('input[name=deliver-if]:checked').val(),
            adtag : adtags,
            qualifier : {
                browse : sayso.temp.qualifier.browse,
                search : sayso.temp.qualifier.search
            }
        };

        var container = $(this).dataContainer();
        // are we editing?
        if (container.getId()) {
            // grab and remove the id (so that it's ready for a new record)
            cell.id = container.getId();
            container.reset();
            // replace the list item
            $('table.cell-lists tbody tr[data-id=' + cell.id + ']').replaceWith(tpl('cellTableRow', cell));
        // or creating a new one?
        } else {
            // generate a unique id
            cell.id = uniqueId();
            // display list item
            $('table.cell-lists tbody').append(tpl('cellTableRow', cell));
        }
        sayso.data.cells[cell.id] = cell;

        sayso.temp.qualifier.browse = {};
        sayso.temp.qualifier.search = {};

        // this effect doesn't work quite right atm
//        $('#build-cells div.section-container').fadeOut(function() {
            $('#build-cells button.reset-input').click();
//            $(this).fadeIn();
//        });
        // reset the cell area
    });

    $('#build-cells button.reset-input').click(function(e){
        e.preventDefault();
        $('#build-cells input[type=text]').val('');
        $('#build-cells input[type=radio]').removeAttr('checked');
        $('#build-cells select').val(false);
        $('#build-cells input[type=checkbox]').removeAttr('checked');

        $('#list-browsing-qualifier').empty();
        $('#list-search-qualifier').empty();

        $('button.build-cell').text('Build Cell');
    });

    $('table.cell-lists a.view').live('click', function (e) {
        e.preventDefault();
        var id = $(this).dataContainer().getId(),
            cellData = sayso.data.cells[id],
            templateData = {
                description: cellData.description,
                type: cellData.type,
                size: cellData.size,
                adTag: [],
                qualifier: {
                    browse: [],
                    search: [],
                    condition: cellData.deliverIf || 'n/a'
                }
            };
          for (var i in cellData.adtag) {
            templateData.adTag.push(cellData.adtag[i].label);
          }
          for (var i in cellData.qualifier.browse) {
            templateData.qualifier.browse.push(cellData.qualifier.browse[i]);
          }
          for (var i in cellData.qualifier.search) {
            var searchData = {
              include: cellData.qualifier.search[i].include,
              term: cellData.qualifier.search[i].term,
              timeframe: cellData.qualifier.search[i].timeframe,
              which: []
            };
            for (var j in cellData.qualifier.search[i].which) {
              searchData.which.push(cellData.qualifier.search[i].which[j]);
            }
            templateData.qualifier.search.push(searchData);
          }
          $('<div id="dialogCellView"></div>')
              .dialog({
                  autoOpen: false,
                  modal: true,
                  resizable: false,
                  title: 'View Cell',
                  width: 800})
              .html(tpl('dialogCellView', templateData))
              .dialog('open');
    });

    $('table.cell-lists a.edit').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId(),
            data = sayso.data.cells[id];
        $('#build-cells').attr('data-id', id);
        $('#cell-description').val(data.description);
        $('input[name=cell-type]').filter('[value=' + data.type + ']').attr('checked',true);
        $('#cell-size').val(data.size);
        // re-check the appropriate adtags
        for (var i in data.adtag) {
            $('div[data-id=' + i + '] input').attr('checked', true);
        }
        // re-display the qualifiers
        for (var i in data.qualifier.browse) {
            sayso.temp.qualifier.browse[i] = data.qualifier.browse[i];
            $(tpl('browsingQualifiers', data.qualifier.browse[i])).appendTo('#list-browsing-qualifier');
        }
        for (var i in data.qualifier.search) {
            sayso.temp.qualifier.search[i] = data.qualifier.search[i];
            $(tpl('searchQualifiers', data.qualifier.search[i])).appendTo('#list-search-qualifier');
        }
        if (data.deliverIf) {
            $('input[name=deliver-if]').filter('[value=' + data.deliverIf + ']').attr('checked', true);
        }
        $('button.build-cell').text('Update Cell');
    });

    $('table.cell-lists a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete this cell "' + container.find('td:first').text() + '"?')) {
            delete sayso.data.cells[id];
            container.removeNow();
        }
    });

    // submit the form
    $('#do-ze-build').click(function (e) {

        e.preventDefault();

        // ad tags
        // (see above)

        // metrics
        if ($('input[name=record-click-track]:checked').length) {
            sayso.data.metrics.clicktrack = $('input[name=record-click-track]:checked').val();
        }
        $('input.record-search-engines:checked').each(function(){
            sayso.data.metrics.searchengines[$(this).parent().find('label').text()] = $(this).is(':checked') ? 'Yes' : 'No';
            sayso.data.metrics.searchengineIds.push($(this).val());
        });
        $('input.record-social:checked').each(function(){
            sayso.data.metrics.social[$(this).parent().find('label').text()] = $(this).is(':checked') ? 'Yes' : 'No';
            sayso.data.metrics.socialIds.push($(this).val());
        });

        // survey
        if ($('input[name=type-survey]:checked').length) {
            sayso.data.surveyinfo.type = $('input[name=type-survey]:checked').val();
        }
        if ($('input[name=type-survey]:checked').val() === 'Custom Survey') {
            sayso.data.surveyinfo.url = $('#type-iframe').val();
        }

        // basic (ad effectiveness)
        if ($('#study-name').val().length) {
            sayso.data.basic.name = $('#study-name').val();
        }
        if ($('#study-id').val().length) {
            sayso.data.basic.id = $('#study-id').val();
        }
        if ($('#study-sample-size').val().length) {
            sayso.data.basic.size = $('#study-sample-size').val();
        }
        if ($('#study-min-threshold').val().length) {
            sayso.data.basic.minimum = $('#study-min-threshold').val();
        }
        if ($('#study-start-date').val().length) {
            sayso.data.basic.begindate = $('#study-start-date').val();
        }
        if ($('#study-end-date').val().length) {
            sayso.data.basic.enddate = $('#study-end-date').val();
        }
        if ($('input[name=study-is-survey]:checked').length) {
            sayso.data.basic.issurvey = $('input[name=study-is-survey]:checked').val();
        }

        // cells
        // (see above)

        var jsonString = JSON.stringify(sayso.data);

        //console.log(jsonString); return false;

        // localStorage
        //localStorage.setItem('sayso', jsonString);

        alert('before send')

        $.ajax({
            url : 'http://' + sayso.baseDomain + '/admin/study/create-new',
            data : {data : jsonString},
            type : 'POST',
            dataType: 'json',
            success : function (response) {
                alert('success')
                console.log(response);                
            }
        });

        /*

        // reset form fields and return "changes pending" to false
        resetForm();

        // reset data object for new data
        resetData();

        // notify the user
        alert('Survey saved!');

        // finally, scroll the view back to the top
        $('html,body').animate({scrollTop:0}, 600);

        */       

    });


    $('#clear-local-data').click(function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to clear local data?')) {
            localStorage.clear();
            // reset radios so browsers don't "remember" the last selection
            $('input[type=radio]').attr('checked',false);
            $('input[type=text]').val('');
            location.reload();
          }
    });

    //    sayso.tagdomain[1].label
    //    sayso.tagdomain[1].tag
    //    sayso.surveyinfo.type
    //    sayso.surveyinfo.deliverIf[1].domain
    //    sayso.metrics.clicktrack
    //    sayso.metrics.searchengines.bing
    //    sayso.metrics.searchengines.google
    //    sayso.metrics.searchengines.yahoo
    //    sayso.metrics.social.facebookLike
    //    sayso.metrics.social.tweet
});