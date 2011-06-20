
(function () {

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
    
    $.fn.dataContainer = function (parentIndex) {
        // get the parent container
        var _container = this.parents('[data-id]').eq(parentIndex ? parentIndex : 0);
        if (!_container.length) {
            // if none found provide harmless object
            _container = {
                attr : function () { return 0; },
                removeAttr : function () {}
            };
        }
        // store off the id
        var _id = _container.attr('data-id');
        // add this as a method on container
        _container.getId = function () {
            return typeof _id === 'undefined' ? 0 : parseInt(_id);
        };
        _container.removeId = function () {
            _container.removeAttr('data-id');
        };
        _container.removeNow = function () {
            _container.fadeOut(function() {
                _container.remove();
            });
        }
        // return the container
        return _container;
    };
    
    function tpl ( tpl, obj, extra ) {
        if (!extra) extra = {};
        data = $.extend({}, obj, extra);
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
    
    function numProperties (obj) {
        var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    }
    
    // ==============================================================
    // sayso data + localstorage
    
    
    if (localStorage.getItem('sayso')) {
        // restore
        sayso.data = JSON.parse(localStorage.getItem('sayso'));
    } else {
        // setup
        sayso.data = {
            tagdomain : {}, 
            metrics : { 
                clicktrack : 'No',
                searchengines : {},
                social : {}
            },
            surveyinfo : {
                type : 'No Survey',
                tag : '',
                deliverIf : {}
            },
            basic : {
                name: '',
                id: '',
                size: '',
                minimum: '',
                begindate: '',
                enddate: '',
                issurvey: 'no'
            },
            cells : {}
        };
    }
    
    // temporary object for storing list data prior to aggregation into sayso.data
    sayso.temp = {
        quota : {},
        qualifier : {
            browse : {},
            search : {}
        },
        domain : {}
    };
    
    // ==============================================================
    // Ad Tags
    
    $('button.add-domain').click(function(e) { 
        e.preventDefault();
        var domain = $('#pairs-domains');
        if (!domain.val().length) {
            alert('Please enter a domain name');
            return;
        }
        var data = {
            name : domain.val()
        };
        data.id = uniqueId();
        sayso.temp.domain[data.id] = data;
        $(tpl('domains', data)).appendTo('#list-domains');
        domain.val('');
    });
    
    $('#list-domains a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        delete sayso.temp.domain[id];
        container.removeNow();
    });
    
    $('button.add-pair').click(function(e){
        e.preventDefault();
        var label = $('#pairs-label');
        var adtag = $('#pairs-ad-tag');
        if (!label.val().length || !adtag.val().length) {
            alert('Ad tag requires a label and ad tag URL');
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
            container.removeId();
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
        $('button.add-pair').text('Add Tag-Domain Pair');
        // add it to/replace it in the data store (by id)
        sayso.data.tagdomain[data.id] = data;
        // reset the fields
        label.val('');
        adtag.val('');
        $('#list-domains li').remove();
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
        if (numProperties(tagdomain.domain)) {
            for (var i in tagdomain.domain) {
                sayso.temp.domain[i] = tagdomain.domain[i];
                $(tpl('domains', tagdomain.domain[i])).appendTo('#list-domains');
            }
        }
        $('button.add-pair').text('Update Tag-Domain Pair');
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
    // Survey
    
    $('input[name=type-survey]').change(function(){
        if ($(this).val() === 'Custom Survey') {
            $('label.paste-iframe-label, textarea#type-iframe').fadeIn('slow');
        } else {
            $('label.paste-iframe-label, textarea#type-iframe').fadeOut('slow');
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
        
        if (!domain.val().length || !timeframe.val().length) {
            alert('Delivery Criteria requires a domain and a time frame');
            return;
        } 
        var data = {
            domain : domain.val(),
            timeframe : timeframe.val()
        };
        data.id = uniqueId();
        sayso.data.surveyinfo.deliverIf[data.id] = data;
        $(tpl('deliveryCriteria', data)).appendTo('#list-survey-delivery-criteria');
        domain.val(false);
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
    // Build Study Cells
    
    // quotas (subset of cells)
    $('#add-quota').click(function(e) {
        e.preventDefault();
        var gender = $('#cell-gender'),
            age = $('#cell-age'),
            percent = $('#cell-size-percent'),
            ethnicity = $('#cell-ethnicity');
        if (!gender.val() && !age.val() && !ethnicity.val()) {
            alert('Quotas must have at least one criteria (gender, age or ethnicity)');
            return;
        }
        var data = {
            gender : gender.val(),
            age : age.val(),
            ethnicity : ethnicity.val(),
            percent : parseInt(percent.val())
            // this may need a prop of 'type'
        };
        data.id = uniqueId();
        sayso.temp.quota[data.id] = data;
        // validate quota percent
        var checkPercent = 0;
        for (var i in sayso.temp.quota) {
            checkPercent += sayso.temp.quota[i].percent;
        }
        if (checkPercent > 100) {
            alert('Total quotas exceeds 100%');
            delete sayso.temp.quota[data.id];
            return;
        }
        // @todo handle missing data required by templates
        $(tpl('quotas', data)).appendTo('#list-cell-quota');
        gender.val(false); age.val(false); ethnicity.val(false); percent.val(false);
    });
    
    $('#list-cell-quota a.delete').live('click', function (e) {
        e.preventDefault();
        var container = $(this).dataContainer(),
            id = container.getId();
        if (confirm('Are you sure you want to delete the quota "' + container.find('span').text() + '"?')) {
            delete sayso.temp.quota[id];
            container.removeNow();
        }
    });
    
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
            timeframe : timeframe.val()
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
            timeframe : timeframe.val(),
            which : {
                bing : $('#engine-bing').is(':checked') ? 'Yes' : 'No',
                google : $('#engine-google').is(':checked') ? 'Yes' : 'No',
                yahoo : $('#engine-yahoo').is(':checked') ? 'Yes' : 'No'
            } // @todo test if this is working correctly (with the template)
        };
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
        if (!numProperties(sayso.temp.quota) || (!numProperties(sayso.temp.qualifier.browse) && !numProperties(sayso.temp.qualifier.search))) {
            alert('Study cell must have quota(s) and qualifier(s)');
            return;
        }
        // validate cell size does not exceed study sample size
        if ($('#study-sample-size').val().length && $('#cell-size').val().length) {
            console.log('validating...');
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
        var adtags = {};
        $('#fieldset-cell-adtags input[type=checkbox]').each(function() {
            var id = $(this).dataContainer().getId();
            if ($(this).is(':checked')) {
                adtags[id] = sayso.data.tagdomain[id];
            } else if (adtags.hasOwnProperty(id)) {
                delete adtags[id];
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
            quota : sayso.temp.quota,
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
            container.removeId();
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
        
        sayso.temp.quota = {};
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
        
        $('#list-cell-quota').empty();
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
                quota: [],
                qualifier: {
                    browse: [],
                    search: [],
                    condition: cellData.deliverIf || 'n/a'
                }
            };
          for (var i in cellData.adtag) {
            templateData.adTag.push(cellData.adtag[i].label);
          }
          for (var i in cellData.quota) {
            templateData.quota.push(cellData.quota[i]);
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
        // re-display the quotas/qualifiers
        for (var i in data.quota) {
            sayso.temp.quota[i] = data.quota[i];
            $(tpl('quotas', data.quota[i])).appendTo('#list-cell-quota');
        }
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
        $('input[name^=record-search-engine]').each(function(){
            sayso.data.metrics.searchengines[$(this).val()] = $(this).is(':checked') ? 'Yes' : 'No';
        });
        $('input[name^=record-social]').each(function(){
            sayso.data.metrics.social[$(this).val()] = $(this).is(':checked') ? 'Yes' : 'No';
        });
        
        // survey
        if ($('input[name=type-survey]:checked').length) {
            sayso.data.surveyinfo.type = $('input[name=type-survey]:checked').val();
        } 
        if ($('input[name=type-survey]:checked').val() === 'Custom Survey') {
            sayso.data.surveyinfo.tag = $('#type-iframe').val();
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
        
        // localStorage
        localStorage.setItem('sayso', JSON.stringify(sayso.data));
        
        alert('Survey saved to local storage!');
        console.log(sayso.data);
    });
    
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
})();