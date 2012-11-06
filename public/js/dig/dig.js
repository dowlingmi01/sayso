/*
dig.js -- the Dynamic Interface Generator
This library serves the dynamic interface generator into elements.
Usually, outside interaction is only with the dig()
function, which is added to the window object below
*/
(function (window, $) {
	var originalCache;
	// *todo* cache should be stored in the topNode itself, but currently is not (that way we can have multiple dig interfaces on the same page)

	/*
	This function starts the Dynamic Interface Generator. Currently creates a <select> tag to choose a top node
	* selectName: required. the name attribute of the created <select> tag
	* containerId: required. the id of the element to contain the top node and top record (e.g. <div id="some_id_here"></div>)
	* recordType: required: the top record type (only 'report_cell' is supported, for now)
	* recordId: required: the top record id (if you want to preselect a specific id)
	* options: optional. parameters used in this function:
		* hidden_by_default: if render a <select tag>
		* single_starbar_id: run in single starbar mode (alters some queries
		* options are also passed throguh to the renderEditorSelect() function that renders the list of report cells, e.g.:
		* label: passed through to renderEditorSelect to render the visible label for the outermost <select>
		* form_id: optional. the id of the form this is contained in (e.g. <form id="some_id_here"></form>)
			the form submit function is overwritten, so that it ensures you save before submitting
	*/
	function dig (containerId, recordType, recordId, options) {
		options = options || {};

		var container = $('#' + containerId);
		container.removeClass('confirm_save edited');
		container.addClass('top_node');
		container.html('');
		if (options.existing_top_node) {
			var topNode = options.existing_top_node;
			topNode.node_info.top_record_type = recordType;
			topNode.node_info.top_record_id = recordId;
			topNode.node_info.element = container;
			options = topNode.start_options;
		} else {
			var topNode = new createEmptyNode(null, 'top', {element: container, top_record_type: recordType, top_record_id: recordId});
			topNode.node_info.element.data('node', topNode);
			topNode.node_info.editable = true;
			topNode.start_options = $.extend({}, options);
		}


		topNode.cache = $.extend(true, {}, originalCache);
		window.liveNodeCache = topNode.cache; // for debugging access...

		if (options.select_name) {
			$.extend(options, {selected_id: recordId});
			topNode.renderEditorSelect(options.select_name, recordType, options);
		} else {
			// loadInto
		}

		// disable the node interface elements before submitting the outer form
		// so they don't get submitted with the outer form
		if (options.form_id) {
			var outerForm = $('#' + options.form_id);
			outerForm.unbind('submit');
			outerForm.submit(function() {
				// disable form submit if something isn't saved
				if (topNode[recordType][topNode.node_info.top_record_id].node_info.updated_since_loading) {
					alert('Please save the user group (or choose another user group) before proceeding');
					return false;
				}
				$('.editor[name*="dig_"]', outerForm).prop('disabled', true);
				// form gets submitted after this function returns
			})
		}

		return topNode;
	}

	// if we create a record in this session, we give it a negative id,
	// which is based on newRecordCounter (i.e. starts with -1, then -2, etc. for each session)
	var newRecordCounter = 1;

	/*
	This is where nodes are created. The general structure for the nodes is:
	top_node <---- only one top node per instance
		|-- properties (form_id, label, hidden_by_default, etc.))
		|-- functions
		|-- child_record_type (this is an object, but not a node)
		|		|-- child_record_id (this is a node)
		|		|		|-- record properties (id, title, compare_report_cell_id, etc.)
		|		|		|-- functions
		|		|		|-- child_record_type
		|		|		|		|-- child_record_id (this is a node)
		|		|		|		+-- ...
		|		|		+-- node_info
		|		+-- child_record_id (this is a node)
		|				|-- ...
		|				+-- ...
		+-- node_info (explained below)

	Other than the top_node, nodes always (at the moment) represent records in the DB
	i.e. some nodes are unmodified records from the database, some are records that have been read and
	updated (updates not yet saved to database), some are created using the interface
	(and usually updated later) and not yet in the database

	Anyway, createEmptyNode() doesn't directly load any record data into the node, it just sets up the
	node_info object (explained next) and the node's functions

	*todo* Function are currently variables inside the instance... instead they should be eventually changed to
	'prototype' functions
	*/
	function createEmptyNode (parent, type, options) {
		if (!options) options = {};

		this.node_info = {
			parent: parent, // a pointer to the parent _node_ (not the container object)
			type: type,  // the record type (e.g. 'report_cell', 'report_cell_user_condition'
			element: options.element, // the html element this record will be rendered in
			editable: options.editable, // can the user edit this node?
			created_since_loading: false, // has this record been created during this session?
			updated_since_loading: false, // has this record been updated during this session?
			deleted_since_loading: false, // has this record been deleted during this session?
			top_record_type: options.top_record_type, // every node in the tree 'knows' the top_record_type and the top_record_id
			top_record_id: options.top_record_id,
			top_node: ( parent ? parent.node_info.top_node : this ) // top of the tree
		}
	}

	createEmptyNode.prototype = {
		// update a field in this record. after updating the field, this function also runs code
		// depending on which table/field is being worked on.
		// E.g. if report_cell_user_condition->condition_type is updated, some interface elements need to be (re)rendered
		// this function is also called (with options.record_not_updated = true)
		// when a record's fields are first rendered, so as to progressively render a record
		updateField: function(editorElement, field, updatedValue, options) {
			options = options || {};

			if (this.node_info.type == 'top') options.record_not_updated = true;

			if (this[field] !== updatedValue && !options.record_not_updated) {
				// the updatedValue is an object, so load each node into the parent
				if (typeof updatedValue == "object") {
					if (options.do_not_mark_parent_updated) { // we are updating a duplicate (i.e. the user updated another node with the same type and id as this one)
						this[field] = {};
						this.node_info.element.children('.node.'+field).not('.create').annihilate();
						for (var c in updatedValue) {
							if (recordSettings[this.node_info.type] && recordSettings[this.node_info.type].parent_id_field) {
								// so getCopyFromCache() knows where to load the record from
								// (e.g. setting options['report_cell_id'] when loading a report_cell_user_condition into a report_cell
								options[recordSettings[this.node_info.type].parent_id_field] = this.id;
							}
							loadInto(this, field, updatedValue[c].id, {do_not_mark_parent_updated: true});
						}
					}
				// regular field, update it with the new value
				} else if (this.node_info.type != 'top') {
					this[field] = updatedValue;
				}
			}

			if ((editorElement && options.do_not_mark_parent_updated) // we are updating a duplicate, see updateDuplicates()
				|| (editorElement && this[field] === updatedValue)) { // or we are reverting a field back to its original value
				editorElement.val(updatedValue);
			}

			// If the field has the clear_next setting, clear the following elements (based on the class specified, if any)
			if (recordSettings[this.node_info.type] &&
				recordSettings[this.node_info.type][field] &&
				typeof recordSettings[this.node_info.type][field].clear_next == "string"
			) {
				editorElement.nextAll( recordSettings[this.node_info.type][field].clear_next ).annihilate(); // remove everything after this element
			}

			// Perform actions based on the field that was updated (and update the updatedValue based on this, in case it's a new id)
			if (recordSettings[this.node_info.type] &&
				recordSettings[this.node_info.type][field] &&
				typeof recordSettings[this.node_info.type][field].onChange == "function"
			) {
				var newUpdatedValue = recordSettings[this.node_info.type][field].onChange(this, editorElement, field, updatedValue, options);
				// if the onChange function returns something, set the updatedValue to it
				if (typeof newUpdatedValue != "undefined") updatedValue = newUpdatedValue;
			}

			// Perform actions based on the type of node that was updated
			if (recordSettings[this.node_info.type] && typeof recordSettings[this.node_info.type].onChange == "function") {
				recordSettings[this.node_info.type].onChange(this, editorElement, field, updatedValue, options);
			}

			// Update the cache, and update duplicates of this node (i.e. nodes that have the same type and id as this one)
			if (!options.record_not_updated && !options.do_not_mark_parent_updated) {
				this.markUpdated(this, editorElement, field, updatedValue, options);
			}
		}, // end of updateField() function

		// this function traverses down the tree recursively, updating any 'duplicates' of updatedOriginal...
		// i.e. other nodes that have the same record type (e.g. 'report_cell') and same id
		updateDuplicates: function (updatedOriginal, editorElement, field, updatedValue, options) {
			if (this == updatedOriginal) return true;
			var childTypes = this.getChildrenThatAreObjects();
			var oldElement = null;
			// childTypes = report_cell_user_conditions (under report_cells) or a report_cell (optionally 1 under a report_cell_user_condition, or 1 under top)
			for (var ct in childTypes) {
				var childType = childTypes[ct]; // childType = 'report_cell_user_condition' or 'report_cell', i.e. an object containing several conditions or report_cells
				for (var childId in this[childType]) {
					if ((childType == updatedOriginal.node_info.type) && (parseInt(childId) == parseInt(updatedOriginal.id)) && (this[childType][childId] != updatedOriginal)) {
						// this[childType][childId] is a duplicate, overwrite with new data
						var duplicate = this[childType][childId];
						$.extend(options, {do_not_mark_parent_updated: true, record_not_updated: false, hidden_by_default: true});
						if (editorElement) {
							var duplicateEditorElement = duplicate.node_info.element.children("." + editorElement.attr('class').split(" ").join(".")).first();
							duplicate.updateField(duplicateEditorElement, field, updatedValue, options);
						} else {
							duplicate.updateField(null, field, updatedValue, options);
						}
					// continue searching recursively on children
					} else {
						this[childType][childId].updateDuplicates(updatedOriginal, editorElement, field, updatedValue, options);
					}
				}
			}
		},

		// this function traverses up the node tree, marking each node along the way as updated,
		// then it calls updateDuplicates when it reaches the top, which traverses down the tree again and updates any 'duplicate' records
		// this function also updates the cache
		markUpdated: function (updatedOriginal, editorElement, field, updatedValue, options) {
			options = options || {};
			this.node_info.updated_since_loading = true;
			this.node_info.element.addClass('edited');

			if (recordSettings[this.node_info.type] &&
				recordSettings[this.node_info.type].markUpdated == "function"
			) {
				recordSettings[this.node_info.type].markUpdated(this, updatedOriginal, editorElement, field, updatedValue, options);
			}

			if (this.node_info.parent && !options.do_not_mark_parent_updated) this.node_info.parent.markUpdated(updatedOriginal, editorElement, field, updatedValue, options);
		},

		// get a copy of this node... used to save to cache, or send via ajax
		// the copy is mostly a copy of the node, but it doesn't have the objects inside
		// node_info (e.g. element, parent), and it can have a limited depth (since the cache storage isn't recursive)
		getSaveData: function (includeFunctions, depth) {
			if (typeof depth == "undefined") depth = 50;
			var saveData = {};

			for (var key in this) {
				if (key == "cache" || key == "start_options" || (typeof this[key] == "function" && !includeFunctions)) {
					continue; // skip
				} else if (key == "node_info") {
					saveData[key] = {};
					for (var c in this[key]) {
						if (typeof this[key][c] != "object") { // don't copy objects inside node_info, e.g. parent, element
							saveData[key][c] = this[key][c];
						}
					}
				} else if ((this[key] != null) && (typeof this[key] == "object") && depth > 0) {
					saveData[key] = {};
					for (var c in this[key]) {
						saveData[key][c] = this[key][c].getSaveData(includeFunctions, depth - 1);
					}
				} else if (typeof this[key] != "object") { //everything else
					saveData[key] = this[key];
				}
			}

			saveData.fully_loaded = true;

			return saveData;
		},

		// check if this node has a parent with the type and id specified
		hasParentsThatMatch: function (type, id) {
			if (this.node_info.parent) {
				if (this.node_info.parent.node_info.type == type && this.node_info.parent.id == id) {
					return true;
				} else {
					return this.node_info.parent.hasParentsThatMatch(type, id);
				}
			}
			return false;
		},

		// get a list of keys in this object that are record collections...
		// e.g., if we are looking at a report_cell that has report_cell_user_condition records below it,
		// this function would return this array 1 element array: ['report_cell_user_condition']
		getChildrenThatAreObjects: function() {
			var children = [];
			for (key in this) {
				if (key != "cache" && key != "node_info" && key != "start_options" && typeof this[key] == "object") {
					children.push(key);
				}
			}
			return children;
		},

		// mark the node deleted (if it is in the db), or just delete it completely if it was created in this session
		remove: function() {
			this.node_info.element.annihilate();
			var parentNode = this.node_info.parent;
			var thisType = this.node_info.type;

			// *todo* only handles duplicates correctly at the moment because it only
			// works on report_cell_user_condition, which can't be shared across report_cells
			if (this.node_info.created_since_loading) { // node has never been saved in db
				delete parentNode[thisType][""+this.id]; // delete node entirely
			} else { // mark deleted so we delete the record when saving to the DB
				this.node_info.deleted_since_loading = true;
				this.markUpdated(this, null, null, null, this, {do_not_mark_parent_updated: true});
			}
			parentNode.markUpdated(parentNode, null, thisType, parentNode[thisType]);
		},

		// this function traverses down the node tree and makes sure that every editor element has a .val()
		validate: function () {
			var validated = true;

			this.node_info.element.children('.editor').each(function(index) {
				var editorElement = $(this);
				if (!editorElement.is('label') && !editorElement.val()) {
					editorElement.css('border', '1px solid red');
					validated = false;
				} else {
					editorElement.css('border', '');
				}
			});

			var childTypes = this.getChildrenThatAreObjects();
			// childTypes = report_cell_user_conditions (under report_cells) or a report_cell (optionally 1 under a report_cell_user_condition, or 1 under top)
			for (var ct in childTypes) {
				var childType = childTypes[ct]; // childType = 'report_cell_user_condition' or 'report_cell', i.e. an object containing several conditions or report_cells
				for (var childId in this[childType]) {
					if (!this[childType][childId].validate()) validated = false;;
				}
			}

			return validated;
		},

		// sends this node (or the getSaveData() version of it) to the server to be saved. the server returns the saved id to be re-loaded at the top.
		saveToServer: function () {
			if (!this.validate()) return;

			var parameters = {
				top_node: this.getSaveData()
			};

			var savedId = null;

			ajaxOptions = {
				data: parameters,
				dataType: 'json',
				type: 'POST',
				async: false,
				url: "/devadmin/dig/ajax-save",
				success: function (responseData) {
					savedId = responseData['saved_id'];
				}
			};
			$.ajax(ajaxOptions);

			// restart the node interface, forcing the cache to be overwritten so it can be read from the server again
			if (savedId) dig(this.node_info.element.attr('id'), this.node_info.top_record_type, savedId, {existing_top_node: this});
			else alert('Save failed :(');

			return savedId;
		},

		// sends this node (or the getSaveData() version of it) to the server to be saved. the server returns success (interface to be reloaded) or not
		reprocessOnServer: function () {
			var successful = false;
			var parameters = {
				report_cell_id: this.node_info.top_record_id
			};

			ajaxOptions = {
				data: parameters,
				dataType: 'json',
				type: 'POST',
				async: false,
				url: "/devadmin/dig/ajax-reprocess",
				success: function (responseData) {
					successful = responseData['successful'];
				}
			};
			$.ajax(ajaxOptions);

			// restart the node interface, forcing the cache to be overwritten so it can be read from the server again
			if (successful) dig(this.node_info.element.attr('id'), this.node_info.top_record_type, this.node_info.top_record_id, {existing_top_node: this});
			else alert('Reprocess failed :(');

			return successful;
		},

		// renders a record... e.g. a report_cell or report_cell_user_condition
		// first it creates the html element and associates it with the node (or empties it if it exists)
		// then inserts the element into the parent, and calls the correct render function based on the type being rendered
		render: function() {
			var updatingExistingElement = false;
			var parentElement = this.node_info.parent.node_info.element;
			var newElement;
			if (this.node_info.element) {
				updatingExistingElement = true;
				newElement = this.node_info.element;
				newElement.html('');
				newElement.removeClass();
			} else {
				newElement = $('<div></div>');
				this.node_info.element = newElement;
			}

			if (this.node_info.deleted_since_loading) return; // this node has been deleted... don't render it

			newElement.addClass(this.node_info.type);
			newElement.addClass('node');

			if (this.node_info.edited_since_loading) {
				newElement.addClass('edited');
			}

			if (parentElement && this.node_info.created_since_loading && $('.create', parentElement).length) {
				parentElement.children('.create').before(newElement);
			} else {
				newElement.appendTo(parentElement);
			}

			if (recordSettings[this.node_info.type] && typeof recordSettings[this.node_info.type].render == "function") {
				recordSettings[this.node_info.type].render(this);
			}
		},

		/*
			This function renders an <select> tag or an <input> tag
			html_element_type: required. 'select' or 'input' (textarea probably would work but not tested)
			field: required. the name of the field, e.g. compare_survey_id
			field_type: optional. used for foreign table, e.g. 'survey'
			options:
				html_before: html string to place before the editorElement
				html_after: html string to place after the editorElement
				label: insert a <label> tag, with text specified by this variable (no <label> tag if blank).
					note that labels always start with a <br>!
				extra_html_attributes: a string extra html attributes to be placed inside the <select>
					or <input> tag, e.g. 'style="border: 2px solid blue"'
		*/
		renderEditorElement: function(html_element_type, field, field_type, options) {
			if (!options) options = {};
			var fieldName = field;
			if (this.id) fieldName += '_'+this.id;
			if (this.node_info.type != 'top') fieldName = 'dig_'+fieldName;

			if (options.html_before) this.node_info.element.append(options.html_before);
			if (options.label) this.node_info.element.append('<label for="'+fieldName+'" class="editor"><br>'+options.label+'</label>');
			if (!options.extra_html_attributes) options.extra_html_attributes = "";
			var editorElement = $('<'+html_element_type+' '+options.extra_html_attributes+' name="'+fieldName+'"></'+html_element_type+'>').appendTo(this.node_info.element);
			if (options.html_after) this.node_info.element.append(options.html_after);

			if (!this.node_info.editable) editorElement.prop('disabled', true);

			editorElement.addClass('editor');
			editorElement.addClass(field);
			editorElement.addClass(this.node_info.type + '__' + field);
			if (field_type) {
				editorElement.addClass(field_type);
			}

			editorElement.data('node', this);
			editorElement.data('field', field);
			editorElement.data('field_type', field_type);

			var node = this;
			editorElement.change(function() {
				// FYI, "this" in this function's context is the editorElement, not the node
				node.updateField(editorElement, field, editorElement.val());
				editorElement.css('border', ''); // clear the red error border
			});

			return editorElement;
		},

		/*
		Renders a <select> tag, with <option> tags inside (loaded from cache, and optionally sorted and filtered)
		field: name of the record field being represented (e.g. compare_survey_id)
		type: the database table represented (e.g. survey)
		options:
			selected_id: optional. set an initial value/option to select
			filter: optional. an array of filter objects, each one is passed to getFilteredArray() independently
			sort: optional. an array of sort objects, each one is passed to getSortedArray() independently
			new_option: optional. a string used to represent the user adding a new record of the type 'type'.
				By default, there is no option for adding a new record
		*/
		renderEditorSelect: function(field, type, options) {
			if (!options) options = {};

			var selectElement = this.renderEditorElement('select', field, type, options);

			var optionsHtml = "";

			var optionsData = getCopyFromCache(this.node_info.top_node.cache,type, null, options);

			if (options.filter) {
				for (var f in options.filter) {
					var filterField = options.filter[f].field;
					var filterValue = options.filter[f].value;
					var filterReverse = options.filter[f].reverse;
					optionsData = getFilteredArray(optionsData, filterField, filterValue, filterReverse);
				}
			}
			if (options.sort) {
				options.sort.reverse(); // perform first sort last since it is the most important
				for (var s in options.sort) {
					var sortField = options.sort[s].field;
					var sortDescending = options.sort[s].descending;
					optionsData = getSortedArray(optionsData, sortField, sortDescending);
				}
			}

			// options are now sorted and filtered as ncessary
			var selectedId = this[field];
			if (options.selected_id) selectedId = options.selected_id;

			var selectedFound, valueChanged;

			if (options.new_option) {
				optionsHtml += "<option value=\"new\""+(selectedId == "new" ? " selected" : "")+">"+options.new_option+"</option>";
			}

			var numberOfOptions = optionsData.length || Object.keys(optionsData).length;

			for (var o in optionsData) {
				var row = optionsData[o];
				if (row['id'] == selectedId || numberOfOptions == 1) {
					if (row['id'] != selectedId) { // numberOfOptions == 1
						selectedId = row['id'];
						valueChanged = true;
					}
					selectedFound = true;
					currentRowSelected = true;
				} else currentRowSelected = false;
				optionsHtml += "<option value=\""+row['id']+"\""+(currentRowSelected ? " selected" : "")+">"+row['label']+"</option>";
			}

			if (numberOfOptions > 1) {
				optionsHtml = '<option>'+(options.initial_value ? options.initial_value : "")+'</option>' + optionsHtml;
			}

			if (valueChanged) {
				this.updateField(selectElement, field, selectedId, {hidden_by_default: this.node_info.top_node.start_options.hidden_by_default});
			} else if (selectedFound) {
				this.updateField(selectElement, field, selectedId, {record_not_updated: true, hidden_by_default: this.node_info.top_node.start_options.hidden_by_default});
			}

			selectElement.html(optionsHtml);
		},

		renderEditorTextInput: function(field, type, options) {
			if (!options) options = {};

			var inputElement = this.renderEditorElement('input', field, type, $.extend({}, options, {extra_html_attributes: 'type="text"'}));

			if (options.placeholder) inputElement.attr('placeholder', options.placeholder);

			var initialValue = this[field];
			if (options.initial_value) initialValue = options.initial_value;

			inputElement.val(initialValue);
		}
	}

	// loads a child node into a node
	function loadInto(parentOfNewNode, type, id, options) {
		options = options || {};
		if (!parentOfNewNode[type]) parentOfNewNode[type] = {};

		options.editable = false;

		var topRecordType = parentOfNewNode.node_info.top_record_type || type;
		var topRecordId = parentOfNewNode.node_info.top_record_id || id;

		options.top_record_type = topRecordType;
		options.top_record_id = topRecordId;

		if (topRecordId == 0) options.top_record_id = (-newRecordCounter);

		var updatingExistingNode = false;
		var newNode;

		if (parentOfNewNode[type][""+id]) {
			parentOfNewNode[type][""+id] = null;
		}

		newNode = new createEmptyNode(parentOfNewNode, type, options);

		var justCreated = false;

		if (id == "new") { // create empty record
			id = newNode.id = -newRecordCounter;
			newRecordCounter++;

			if (recordSettings[type]) {
				for (var field in recordSettings[type]) {
					if (typeof recordSettings[type][field] == "object" && typeof recordSettings[type][field]['default_value'] != "undefined") {
						newNode[field] = recordSettings[type][field]['default_value'];
					}
				}
				if (recordSettings[type]['parent_id_field']) {
					newNode[recordSettings[type]['parent_id_field']] = parentOfNewNode.id;
				}
			}

			justCreated = true;
		} else { // load existing
			if (recordSettings[type] && typeof recordSettings[type].getLoadIntoParameters == "function") {
				$.extend(options, recordSettings[type].getLoadIntoParameters(parentOfNewNode, id, options));
			}
			$.extend(true, newNode, getCopyFromCache(parentOfNewNode.node_info.top_node.cache, type, id, options));
		}

		if (id < 0) { // node was created in this session, either just now, or previouly in this session
			newNode.node_info.created_since_loading = true;
			newNode.node_info.updated_since_loading = true;
		}

		if (
			newNode.node_info.created_since_loading
			|| (
				recordSettings[type]
				&& typeof recordSettings[type].isEditable == "function"
				&& recordSettings[type].isEditable(newNode, parentOfNewNode, id, options)
			)
		) {
			newNode.node_info.editable = true;
			options.editable = true;
		} else {
			options.editable = false;
		}

		var childTypes = newNode.getChildrenThatAreObjects();
		// childTypes = report_cell_user_conditions (under report_cells) or a report_cell (optionally 1 under a report_cell_user_condition)
		for (var ct in childTypes) {
			var childType = childTypes[ct]; // child_type = 'report_cell_user_condition' or 'report_cell', i.e. an object containing several conditions or report_cells
			for (var childId in newNode[childType]) {
				var newChildNode = new createEmptyNode(newNode, childType, options);
				newNode[childType][childId] = $.extend(true, newChildNode, newNode[childType][childId]);
			}
		}

		parentOfNewNode[type][""+id] = newNode;

		newNode.render();

		if (!options.do_not_mark_parent_updated) {
			newNode.markUpdated(newNode, null, null, null, {do_not_mark_parent_updated: true});
			parentOfNewNode.markUpdated(parentOfNewNode, null, type, parentOfNewNode[type]);
		}

		if (newNode.node_info.created_since_loading || newNode.node_info.updated_since_loading) {
			newNode.node_info.element.addClass('edited');
		}

		return newNode.id;

	}


	function updateFieldToNewId(node, editorElement, field, newId, newLabel) {
		node[field] = newId;
		if (editorElement.is('select')) {
			var selectClass = node.node_info.type + '__' + field;
			var selectsToChange = $('select.' + selectClass);
			var newOptionHtml = '<option value="'+newId+'">'+newLabel+'</option>';
			selectsToChange.each(function (index) {
				$('option[value = "new"]', $(this)).after($(newOptionHtml));
			});
			editorElement.val(newId);
		}
	}

	function filterObjects(obj, filterField, filterValue, reverse) {
		var result = false;
		if (typeof obj === "object" && obj[filterField] == filterValue) result = true;
		if (reverse) result = !result;
		return result;
	}

	/* Example:
	obj = {
		"1": {id: 1, type: "good"},
		"2": {id: 2, type: "bad"}
		"3": {id: 3, type: "good"}
	}
	calling: getFilteredArray(obj, 'type', 'good')  or  getFilteredArray(obj, 'type', 'bad', true)
	returns: [{id: 1, type: "good"}, {id: 3, type: "good"}]

	calling: getFilteredArray(obj, 'type', 'bad')  or  getFilteredArray(obj, 'type', 'good', true)
	returns: [{id: 2, type: "bad"}]
	*/
	function getFilteredArray(obj, filterField, filterValue, reverse) {
		var results = [];
		var index = 0
		for (key in obj) {
			results[index] = obj[key];
			index++;
		}
		results = results.filter(function(a) { return filterObjects(a, filterField, filterValue, reverse); });
		return results;
	}

	function sortObjects(a, b, sortField, descending) {
		if (typeof a !== "object" || typeof a[sortField] === "undefined") return 0;
		if (typeof a[sortField] !== typeof b[sortField]) return 0;
		var valueA = a[sortField];
		var valueB = b[sortField];
		if (typeof valueA === "string") {
			descending = !descending;
			valueA = valueA.toLowerCase();
			valueB = valueB.toLowerCase();
		}
		if ((valueA < valueB && !descending) || (valueA > valueB && descending))return -1;
		if ((valueA > valueB && !descending) || (valueA < valueB && descending))return 1;
		return 0;
	}

	/* Example:
	obj = {
		"1": {id: 1, title: "Worst Movie"},
		"2": {id: 2, title: "Best Movie"}
	}
	calling: getSortedArray(obj, 'title')  or  getSortedArray(obj, 'id', true)
	returns: [{id: 2, title: "Best Movie"}, {id: 1, title: "Worst Movie"}]

	calling: getSortedArray(obj, 'id')
	returns: [{id: 1, title: "Worst Movie"}, {id: 2, title: "Best Movie"}]
	*/
	function getSortedArray(obj, field, descending) {
		var results = [];
		var index = 0
		for (key in obj) {
			results[index] = obj[key];
			index++;
		}
		results.sort(function(a, b) { return sortObjects(a, b, field, descending); });
		return results;
	}


	function getCopyFromCache(cache, type, id, options) {
		if (!options) options = {};
		var cacheLocation = null;
		var originalData = false;

		if (recordSettings[type] && typeof recordSettings[type].getCacheLocation == "function") {
			cacheLocation = recordSettings[type].getCacheLocation(cache, id, options);
		}

		if (!cacheLocation) return false;

		if (typeof cacheLocation[type] === "undefined" && id) { // we are grabbing a single record into a type that hasn't been added to the cache yet
			cacheLocation[type] = {};
		}

		if (!id) { // get list
			if (typeof cacheLocation[type] === "undefined" || !Object.keys(cacheLocation[type]).length) {
				cacheLocation[type] = getFromServer(type, null, options)
			}

			originalData = cacheLocation[type];

		} else { // get record
			// cacheLocation[type] should always be an object by this point
			if (typeof cacheLocation[type][id] === "undefined" || (!cacheLocation[type][id].fully_loaded)) {
				$.extend(true, cacheLocation[type], getFromServer(type, id, options));
				cacheLocation[type][id].fully_loaded = true;
			}
			originalData = cacheLocation[type][id];

		}

		if (originalData) {
			var copy = $.extend(true, {}, originalData);
			// the field fully_loaded is only there for the cache, and is not part of the record, so remove it from the copy before returning
			if (typeof copy.fully_loaded != "undefined") delete copy.fully_loaded;
			return copy;
		}

		return false;
	}

	function getFromServer(type, id, options) {
		if (!options) options = {};
		var cacheLocation = null;
		var parameters = {
			record_type: type,
			record_id: (id || -1)
		};

		if (recordSettings[type] && typeof recordSettings[type].getServerRequestParameters == "function") {
			var extraParameters = recordSettings[type].getServerRequestParameters(id, options);
			if (extraParameters) {
				if (typeof extraParameters == "object") {
					$.extend(parameters, extraParameters);
				}
			} else {
				return;
			}
		} else {
			return;
		}

		var ajaxResponse = null;

		ajaxOptions = {
			data: parameters,
			dataType: 'json',
			async: false,
			type: 'POST',
			url: "/devadmin/dig/ajax-load",
			success: function (responseData) {
				ajaxResponse = responseData;
			}
		};
		$.ajax(ajaxOptions);

		return ajaxResponse;
	}

	function log() {
		if (window.console && window.console['log']) {
			var args = Array.prototype.slice.call(arguments);
			window.console['log'].apply(window.console, args);
		}
	}

	function getStarbarIdForSurvey(cache, surveyId) {
		getCopyFromCache(cache, 'starbar', null); // in case not loaded from server
		var survey = getCopyFromCache(cache, 'survey', surveyId);
		for (var surveyStarbar in survey.starbar) {
			for (var cachedStarbar in cache.starbar) {
				if (surveyStarbar == cachedStarbar) return cachedStarbar;
			}
		}
		return false;
	}

	// virtual db records, used mostly for enums and things of the sort
	originalCache = {
		report_cell_condition_type: {
			and: { id: "and", label: "and" },
			or:	{ id: "or", label: "or" }
		},
		report_cell_user_condition_condition_type: {
			report_cell: {
				id: "report_cell",
				label: "User Group",
				comparison_type: {
					"in": {
						id: "in",
						label: "in"
					},
					"not in": {
						id: "not in",
						label: "not in"
					}
				},
				fields: [ "compare_report_cell_id" ],
				primary: true
			},
			starbar: {
				id: "starbar",
				label: "Panel",
				comparison_type: {
					"in": {
						id: "in",
						label: "in"
					},
					"not in": {
						id: "not in",
						label: "not in"
					}
				},
				fields: [ "compare_starbar_id" ],
				primary: true
			},
			study_ad: {
				id: "study_ad",
				label: "Study Ad",
				comparison_type: {
					"viewed": {
						id: "viewed",
						label: "viewed"
					},
					"clicked": {
						id: "clicked",
						label: "clicked"
					}
				},
				fields: [ "compare_study_ad_id" ],
				primary: true
			},
			question: {
				id: "question",
				label: "Survey Question",
				// comparison_types derived from the survey_question choice_type and data_type
				primary: true
			},
			single: {
				id: "single",
				label: "single",
				comparison_type: {
					"=": {
						id: "=",
						label: "="
					},
					"!=": {
						id: "!=",
						label: "!="
					}
				},
				fields: [ "compare_survey_question_id", "compare_survey_question_choice_id" ],
				primary: false
			},
			multiple: {
				id: "multiple",
				label: "multiple",
				comparison_type: {
					"in": {
						id: "in",
						label: "in"
					},
					"not in": {
						id: "not in",
						label: "not in"
					}
				},
				fields: [ "compare_survey_question_id", "compare_string" ],
				primary: false
			},
			string: {
				id: "string",
				label: "string",
				comparison_type: {
					"=": {
						id: "=",
						label: "is exactly"
					},
					"!=": {
						id: "!=",
						label: "is not exactly"
					},
					"contains": {
						id: "contains",
						label: "contains"
					},
					"does not contain": {
						id: "does not contain",
						label: "does not contain"
					}
				},
				fields: [ "compare_survey_question_id", "compare_string" ],
				primary: false
			},
			integer: {
				id: "integer",
				label: "integer",
				comparison_type: {
					"=": {
						id: "=",
						label: "="
					},
					"!=": {
						id: "!=",
						label: "!="
					},
					">": {
						id: ">",
						label: ">"
					},
					">=": {
						id: ">=",
						label: ">="
					},
					"<": {
						id: "<",
						label: "<"
					},
					"<=": {
						id: "<=",
						label: "<="
					}
				},
				fields: [ "compare_survey_question_id", "compare_integer" ],
				primary: false
			},
			decimal: {
				id: "decimal",
				label: "decimal",
				comparison_type: {
					"=": {
						id: "=",
						label: "="
					},
					"!=": {
						id: "!=",
						label: "!="
					},
					">": {
						id: ">",
						label: ">"
					},
					">=": {
						id: ">=",
						label: ">="
					},
					"<": {
						id: "<",
						label: "<"
					},
					"<=": {
						id: "<=",
						label: "<="
					}
				},
				fields: [ "compare_survey_question_id", "compare_decimal" ],
				primary: false
			},
			monetary: {
				id: "monetary",
				label: "monetary",
				comparison_type: {
					"=": {
						id: "=",
						label: "="
					},
					"!=": {
						id: "!=",
						label: "!="
					},
					">": {
						id: ">",
						label: ">"
					},
					">=": {
						id: ">=",
						label: ">="
					},
					"<": {
						id: "<",
						label: "<"
					},
					"<=": {
						id: "<=",
						label: "<="
					}
				},
				fields: [ "compare_survey_question_id", "compare_decimal" ],
				primary: false
			},
			survey_status: {
				id: "survey_status",
				label: "Survey Status",
				comparison_type: {
					"=": {
						id: "=",
						label: "="
					},
					"!=": {
						id: "!=",
						label: "!="
					}
				},
				// possible survey statuses:
				compare_string_choice: {
					"new": {
						id: "new",
						label: "new"
					},
					"archived": {
						id: "archived",
						label: "archived"
					},
					"completed": {
						id: "completed",
						label: "completed"
					},
					"disqualified": {
						id: "disqualified",
						label: "disqualified"
					}
				},
				fields: [ "compare_survey_question_id", "compare_string" ],
				primary: true
			}
		},
		survey_type: {
			"survey": {
				id: "survey",
				label: "Survey",
				type: "survey"
			},
			"poll": {
				id: "poll",
				label: "Poll",
				type: "poll"
			},
			"trailer": {
				id: "trailer",
				label: "Trailer",
				type: "trailer"
			},
			"mission": {
				id: "mission",
				label: "Mission",
				type: "mission"
			}/*,
			"quiz": {
				id: "quiz",
				label: "Quiz",
				type: "quiz"
			}*/
		}
	};

	var recordSettings = {
		top: {
			markUpdated: function (topNode, updatedOriginal, editorElement, field, updatedValue, options) {
				topNode.updateDuplicates(updatedOriginal, editorElement, field, updatedOriginal[field], options);
			},
			onChange: function (topNode, editorElement, field, updatedValue, options) {
				options.do_not_mark_parent_updated = true; // stop markUpdated() from being called at the end of updateField
			},
			report_cell_id: {
				clear_next: '', // remove everything after the editorElement when this field changes
				onChange: function (topNode, editorElement, field, updatedValue, options) {
					topNode.report_cell = {}; // remove the old report_cell from the node

					if (updatedValue) {
						topNode.node_info.element.removeClass('edited');

						// these are set when a new report_cell is added
						var newId = 0;
						var newLabel = "";

						if (updatedValue != "new") topNode.node_info.top_record_id = updatedValue;
						newId = loadInto(topNode, topNode.node_info.top_record_type, updatedValue, {do_not_mark_parent_updated: true}) + "";

						var reportCell = topNode[topNode.node_info.top_record_type][newId];

						if (updatedValue == "new") {
							topNode.node_info.top_record_id = newId;
							newLabel = 'CUSTOM: Untitled User Group ' + newId;
							reportCell.updateField(null, 'label', newLabel, {do_not_mark_parent_updated: true});
							reportCell.updateField(null, 'ordinal', newId * 10, {do_not_mark_parent_updated: true});
							reportCell.markUpdated(null, null, null, null, {do_not_mark_parent_updated: true}); // save in cache

							updateFieldToNewId(topNode, editorElement, field, newId, newLabel);
							updatedValue = newId;
						}

						var saveLinkContainer = $('<div></div>').addClass('save');
						var saveLink = $('<a href="#">Save</a>');

						saveLink.mouseover(function () {
							topNode.node_info.element.addClass('confirm_save');
						});
						saveLink.mouseout(function () {
							topNode.node_info.element.removeClass('confirm_save');
						});
						saveLink.click(function () {
							topNode.saveToServer();
							return false;
						});

						saveLinkContainer.append(saveLink);
						topNode[topNode.node_info.top_record_type][topNode.node_info.top_record_id].node_info.element.prepend(saveLinkContainer);


						var reprocessLinkContainer = $('<div></div>').addClass('reprocess');
						var reprocessLink = $('<a href="#">Refresh Number of Users</a>');

						reprocessLink.mouseover(function () {
							topNode.node_info.element.addClass('confirm_save');
						});
						reprocessLink.mouseout(function () {
							topNode.node_info.element.removeClass('confirm_save');
						});
						reprocessLink.click(function () {
							topNode.reprocessOnServer();
							return false;
						});

						reprocessLinkContainer.append(reprocessLink);
						topNode[topNode.node_info.top_record_type][topNode.node_info.top_record_id].node_info.element.prepend(reprocessLinkContainer);


						var toggleViewContainer = $('<span class="toggle_view"></span>');
						var toggleViewLink = $('<a href="#">hide conditions</a>');
						toggleViewLink.click(function() {
							if (reportCell.node_info.hidden_conditions) { // currently hidden
								reportCell.node_info.element.removeClass('hidden');
								toggleViewLink.html('hide conditions');
								reportCell.node_info.hidden_conditions = false;
							} else { // currently shown
								reportCell.node_info.element.addClass('hidden');
								toggleViewLink.html('show conditions');
								reportCell.node_info.hidden_conditions = true;
							}
							return false;
						});
						toggleViewContainer.append('(').append(toggleViewLink).append(')');
						reportCell.node_info.element.before(toggleViewContainer);

						if (topNode.start_options.hidden_by_default && (!reportCell.node_info.updated_since_loading)) {
							toggleViewLink.trigger('click');
						}
					}

					return updatedValue;
				} // end of top.report_cell_id.onChange
			} // end of top.report_cell_id
		},

		// =====================================

		report_cell: {
			isEditable: function (newNode, parentOfNewNode, id, options) {
				if ( newNode.category == 'Custom' ) return true; // can only edit custom report_cells
			},
			getServerRequestParameters: function (id, options) {
				var extraParameters = { single_starbar_id: options.single_starbar_id };
				return extraParameters;
			},
			getCacheLocation: function (cache, id, options) {
				return cache;
			},
			markUpdated: function (reportCell, updatedOriginal, editorElement, field, updatedValue, options) {
				reportCell.node_info.top_node.cache['report_cell'][reportCell.id] = reportCell.getSaveData(false, 1);
			},
			render: function(reportCell) {
				reportCell.renderEditorTextInput('title', null, {placeholder: 'Title for user group (e.g. Males 18-24)'});
				reportCell.renderEditorSelect('condition_type', 'report_cell_condition_type');

				for (var c in reportCell.report_cell_user_condition) {
					var condition = reportCell.report_cell_user_condition[c];
					condition.render();
				}

				if (reportCell.node_info.editable) {
					var createNewConditionElement = $('<div><div class="vertical_line"><div class="horizontal_line"></div></div></div>').addClass('node report_cell_user_condition create');
					var createNewConditionLink = $('<a href="#">Add new condition</a>');

					createNewConditionLink.click(function() {
						loadInto(reportCell, 'report_cell_user_condition', 'new');
						return false;
					});
					createNewConditionElement.append(createNewConditionLink);
					reportCell.node_info.element.append(createNewConditionElement);
				}

				if ($('div.node', reportCell.node_info.element).length) {
					var lastSubNode = $('div.node', reportCell.node_info.element).last();
					var extraLineHeight = lastSubNode.outerHeight() - 41;

					var hideExtraLineElement = $('<div></div>').addClass('node hide_line');
					hideExtraLineElement.css({
						'height': extraLineHeight + 'px',
						'margin-top': '-' + extraLineHeight + 'px'
					});
					reportCell.node_info.element.append(hideExtraLineElement);
				}
			},
			title: {
				onChange: function (reportCell, editorElement, field, updatedValue, options) {
					if (!updatedValue) updatedValue = "Untitled User Group " + reportCell.id
					var newText = reportCell.category.toUpperCase() + ': ' + updatedValue + ' (' + reportCell.number_of_users + ' users)';

					var optionsToChange = $('option[value = "'+reportCell.id+'"]', 'select.editor.report_cell');
					optionsToChange.text(newText);

					reportCell.updateField(null, 'label', newText, $.extend({}, options));
				}
			},
			category: {
				default_value: 'custom'
			},
			processing_type: {
				default_value: 'automatic'
			},
			conditions_processed: {
				default_value: 0
			},
			number_of_users: {
				default_value: 0
			},
			condition_type: {
				default_value: 'and'
			}
		},

		// =====================================

		report_cell_user_condition: {
			isEditable: function (newNode, parentOfNewNode, id, options) {
				if (parentOfNewNode.node_info.editable) return true;
			},
			getLoadIntoParameters: function (parentOfNewNode, id, options) {
				return { report_cell_id: parentOfNewNode.id };
			},
			getCacheLocation: function (cache, id, options) {
				if (options.report_cell_id) {
					return cache.report_cell[options.report_cell_id+""];
				}
			},
			markUpdated: function (condition, updatedOriginal, editorElement, field, updatedValue, options) {
				if (condition.report_cell && condition.report_cell[condition.compare_report_cell_id]) {
					condition.node_info.top_node.cache['report_cell'][condition.compare_report_cell_id + ""] = condition.report_cell[condition.compare_report_cell_id].getSaveData(false, 1);
				}
				if (!condition.node_info.top_node.cache['report_cell'][condition.node_info.parent.id]['report_cell_user_condition']) condition.node_info.top_node.cache['report_cell'][condition.node_info.parent.id]['report_cell_user_condition'] = {}
				condition.node_info.top_node.cache['report_cell'][condition.node_info.parent.id]['report_cell_user_condition'][condition.id] = condition.getSaveData(false, 0);
			},
			render: function(condition) {
				condition.node_info.element.html('<div class="vertical_line"><div class="horizontal_line"></div></div>Condition type ');
				var selectedConditionType = condition.condition_type;
				if (condition.node_info.top_node.cache.report_cell_user_condition_condition_type[condition.condition_type] && condition.node_info.top_node.cache.report_cell_user_condition_condition_type[condition.condition_type].primary == false) {
					selectedConditionType = "question"; // the condition_type is later derived from the question's type (single, multiple, integer, decimal, monetary, string)
				}
				condition.renderEditorSelect('condition_type', 'report_cell_user_condition_condition_type', {selected_id: selectedConditionType, filter: [{field: 'primary', value: true}], html_after: "<br />"});

				if (condition.node_info.editable) {
					var deleteLink = $('<a href="#">delete</a>');
					deleteLink.css({
						'float': 'right',
						'margin-right': '40px'
					});
					deleteLink.mouseover(function () {
						condition.node_info.element.addClass('confirm_delete');
					});
					deleteLink.mouseout(function () {
						condition.node_info.element.removeClass('confirm_delete');
					});
					deleteLink.click(function () {
						condition.remove();
						return false;
					});
					condition.node_info.element.prepend(deleteLink);
				}
			},

			/* optional parent id field */
			parent_id_field: 'report_cell_id',

			/* other defaults */
			condition_type: {
				default_value: 'report_cell',
				clear_next: '', // remove everything after the editorElement when this field changes
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						switch (updatedValue) { // rebuild interface as needed
							case "question":
								if (condition.compare_survey_question_id && !condition.compare_survey_question_survey_id) {
									var surveyQuestion = getCopyFromCache(condition.node_info.top_node.cache, 'survey_question', condition.compare_survey_question_id);
									var survey = getCopyFromCache(condition.node_info.top_node.cache, 'survey', surveyQuestion.survey_id);
									condition.compare_survey_question_survey_id = survey.id;
									condition.compare_survey_question_survey_type = survey.type;
									condition.compare_survey_question_starbar_id = condition.node_info.top_node.start_options.single_starbar_id || getStarbarIdForSurvey(condition.node_info.top_node.cache, survey.id);
									options.record_not_updated = true;
								}
								condition.renderEditorSelect('compare_survey_question_starbar_id', 'starbar', {single_starbar_id: condition.node_info.top_node.start_options.single_starbar_id, label: "<span class='detail'>Filters (survey question location): </span> "});
								break;
							case "survey_status":
								if (condition.compare_survey_id) {
									var survey = getCopyFromCache(condition.node_info.top_node.cache, 'survey', condition.compare_survey_id);
									condition.compare_survey_starbar_id = condition.node_info.top_node.start_options.single_starbar_id || getStarbarIdForSurvey(condition.node_info.top_node.cache, survey.id);
									condition.compare_survey_type = survey.type;
									options.record_not_updated = true;
								}
								condition.renderEditorSelect('compare_survey_starbar_id', 'starbar', {single_starbar_id: condition.node_info.top_node.start_options.single_starbar_id, label: "<span class='detail'>Filters (survey location): </span> "});
								break;
							case "starbar":
								condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "Match users who are "});
								condition.renderEditorSelect('compare_starbar_id', 'starbar', {single_starbar_id: condition.node_info.top_node.start_options.single_starbar_id});
								break;
							case "study_ad":
								condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "Match users who have "});
								condition.renderEditorSelect('compare_study_ad_id', 'study_ad');
								break;
								break;
							case "report_cell":
								condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "Match users who are "});
								var topReportCellId = (condition.node_info.top_node.node_info.top_record_type == "report_cell" && condition.node_info.top_node.node_info.top_record_id > 0 ? condition.node_info.top_node.node_info.top_record_id : null);
								condition.renderEditorSelect('compare_report_cell_id', 'report_cell', {single_starbar_id: condition.node_info.top_node.start_options.single_starbar_id, sort:[{field: 'ordinal'}], new_option: "New User Group"});
								break;
							default:
								break;
						}
					}
				}
			},
			comparison_type: {
				default_value: 'in'
			},
			compare_survey_type: {
				clear_next: '.editor', // remove everything after editorElement with the class 'editor' when this field changes
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						condition.renderEditorSelect('compare_survey_id', 'survey', { starbar_id: condition.compare_survey_starbar_id, filter: [{field: 'type', value: updatedValue}], label: "<br />Match users who saw this " + updatedValue+ " "});
					}
				}
			},
			compare_survey_starbar_id: {
				clear_next: '.editor',
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						condition.renderEditorSelect('compare_survey_type', 'survey_type', {starbar_id: updatedValue});
					}
				}
			},
			compare_survey_id: {
				clear_next: '.editor',
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "and whose current survey status "});
						condition.renderEditorSelect('compare_string', 'compare_string_choice', {report_cell_user_condition_condition_type: condition.condition_type});
					}
				}
			},
			compare_survey_question_starbar_id: {
				clear_next: '.editor',
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						condition.renderEditorSelect('compare_survey_question_survey_type', 'survey_type', {starbar_id: (updatedValue || condition.node_info.top_node.start_options.single_starbar_id)});
					}
				}
			},
			compare_survey_question_survey_type: {
				clear_next: '.editor',
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						condition.renderEditorSelect('compare_survey_question_survey_id', 'survey', {starbar_id: (condition.compare_survey_question_starbar_id || condition.node_info.top_node.start_options.single_starbar_id), filter: [{field: 'type', value: updatedValue}]});
					}
				}
			},
			compare_survey_question_survey_id: {
				clear_next: '.editor',
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						condition.renderEditorSelect('compare_survey_question_id', 'survey_question', {selected_id: condition.condition_type + '-' + condition.compare_survey_question_id, starbar_id: condition.compare_survey_question_starbar_id, survey_id: condition.compare_survey_question_survey_id, label: "<br />Match users who answered "});
					}
				}
			},
			compare_survey_question_id: {
				clear_next: '.editor',
				onChange: function (condition, editorElement, field, updatedValue, options) {
					if (updatedValue) {
						var updatedValues = updatedValue.split("-");
						condition.condition_type = updatedValues[0];
						condition.compare_survey_question_id = updatedValues[1];

						switch (condition.condition_type) {
							case "single":
								condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "and whose choice "});
								var surveyQuestion = getCopyFromCache(condition.node_info.top_node.cache, 'survey_question', condition.compare_survey_question_id);
								condition.renderEditorSelect('compare_survey_question_choice_id', 'survey_question_choice', {survey_question_id: condition.compare_survey_question_id});
								break;
							case "multiple":
								var surveyQuestion = getCopyFromCache(condition.node_info.top_node.cache, 'survey_question', condition.compare_survey_question_id);
								break;
							case "integer":
								condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "and who entered a value "});
								condition.renderEditorTextInput('compare_integer', null);
								break;
							case "decimal":
							case "monetary":
								condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "and who entered a value "});
								condition.renderEditorTextInput('compare_decimal');
								break;
							case "string":
								condition.renderEditorSelect('comparison_type', 'comparison_type', {report_cell_user_condition_condition_type: condition.condition_type, label: "and who entered something that "});
								condition.renderEditorTextInput('compare_string');
								break;
							default:
								break;
						}
					}
				}
			},
			compare_report_cell_id: {
				clear_next: '',
				onChange: function (condition, editorElement, field, updatedValue, options) {
					condition.report_cell = {}; // remove the old report_cell from the node

					if (updatedValue) {
						if (condition.hasParentsThatMatch('report_cell', updatedValue)) {
							alert('A user group cannot be inside itself!');
							condition.updateField(editorElement, field, '', options);
							return;
						}
						newId = loadInto(condition, 'report_cell', updatedValue, {do_not_mark_parent_updated: true}) + "";
						var reportCell = condition['report_cell'][newId];

						if (updatedValue == "new") {
							newLabel = 'CUSTOM: Untitled User Group ' + newId;
							reportCell.updateField(null, 'label', newLabel, {do_not_mark_parent_updated: true});
							reportCell.updateField(null, 'ordinal', newId * 10, {do_not_mark_parent_updated: true});
							reportCell.markUpdated(null, null, null, null, {do_not_mark_parent_updated: true}); // save in cache

							updateFieldToNewId(condition, editorElement, field, newId, newLabel);
							updatedValue = newId;
						}

						var toggleViewContainer = $('<span class="toggle_view"></span>');
						var toggleViewLink = $('<a href="#">hide conditions</a>');
						toggleViewLink.click(function() {
							if (reportCell.node_info.hidden_conditions) { // currently hidden
								reportCell.node_info.element.removeClass('hidden');
								toggleViewLink.html('hide conditions');
								reportCell.node_info.hidden_conditions = false;
							} else { // currently shown
								reportCell.node_info.element.addClass('hidden');
								toggleViewLink.html('show conditions');
								reportCell.node_info.hidden_conditions = true;
							}
							return false;
						});
						toggleViewContainer.append('(').append(toggleViewLink).append(')');
						reportCell.node_info.element.before(toggleViewContainer);

						if (options.hidden_by_default || !reportCell.node_info.editable) {
							toggleViewLink.trigger('click');
						}
					}

					return updatedValue;
				}
			}
		},

		// =====================================

		report_cell_condition_type: {
			getCacheLocation: function (cache, id, options) {
				return cache;
			}
		},

		// =====================================

		report_cell_user_condition_condition_type: {
			getCacheLocation: function (cache, id, options) {
				return cache;
			}
		},

		// =====================================

		study_ad: {
			getServerRequestParameters: function (id, options) {
				return true;
			},
			getCacheLocation: function (cache, id, options) {
				return cache;
			}
		},

		// =====================================

		survey_type: {
			getCacheLocation: function (cache, id, options) {
				return cache;
			}
		},

		// =====================================

		starbar: {
			getServerRequestParameters: function (id, options) {
				return { single_starbar_id: options.single_starbar_id };
			},
			getCacheLocation: function (cache, id, options) {
				return cache;
			}
		},

		// =====================================

		survey: {
			getServerRequestParameters: function (id, options) {
				if (!id && !options.starbar_id) return false;
				if (options.starbar_id) return { starbar_id: options.starbar_id };
				return true;
			},
			getCacheLocation: function (cache, id, options) {
				if (id && cache['survey'] && cache['survey'][id]) { // id specified, and it is cached
					return cache;
				} else if (options.starbar_id) { // starbar specified
					return cache.starbar[options.starbar_id+""];
				} else if (id) { // id specified, not cached
					return cache;
				}
			}
		},

		// =====================================

		survey_question: {
			getServerRequestParameters: function (id, options) {
				if (!id && !options.survey_id) return false;
				return { survey_id: options.survey_id };
			},
			getCacheLocation: function (cache, id, options) {
				if (id) {
					return cache;
				} else if (options.starbar_id && options.survey_id) {
					return cache.starbar[options.starbar_id+""].survey[options.survey_id+""];
				}
			}
		},

		// =====================================

		survey_question_choice: {
			getCacheLocation: function (cache, id, options) {
				if (options.survey_question_id) {
					return cache.survey_question[options.survey_question_id];
				}
			}
		},

		// =====================================

		comparison_type: {
			getCacheLocation: function (cache, id, options) {
				if (options.report_cell_user_condition_condition_type) {
					return cache.report_cell_user_condition_condition_type[options.report_cell_user_condition_condition_type+""];
				}
			}
		},

		// =====================================

		compare_string_choice: {
			getCacheLocation: function (cache, id, options) {
				if (options.report_cell_user_condition_condition_type) {
					return cache.report_cell_user_condition_condition_type[options.report_cell_user_condition_condition_type+""];
				}
			}
		},
	}


	/* set window variables */
	window.originalNodeCache = originalCache; // In case we want to pre-cache on the page before starting node interface
	window.dig = dig;



	/*
	* This function takes two parameters: integer value for string length and optional
	* boolean value true if you want to include special characters in your generated string.
	* From: http://jquery-howto.blogspot.com/2009/10/javascript-jquery-password-generator.html
	*/
	function getRandomString(length, special) {
		var iteration = 0;
		var randomString = "";
		var randomNumber;
		if(typeof special === "undefined"){
			var special = false;
		}
		while(iteration < length){
			randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
			if(!special){
				if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
				if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
				if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
				if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
			}
			iteration++;
			randomString += String.fromCharCode(randomNumber);
		}
		return randomString;
	}

	/**
	 * Copyright (c) Mozilla Foundation http://www.mozilla.org/
	 * This code is available under the terms of the MIT License
	 */
	if (!Array.prototype.filter) {
		Array.prototype.filter = function(fun /*, thisp*/) {
			var len = this.length >>> 0;
			if (typeof fun != "function") {
				throw new TypeError();
			}

			var res = [];
			var thisp = arguments[1];
			for (var i = 0; i < len; i++) {
				if (i in this) {
					var val = this[i]; // in case fun mutates this
					if (fun.call(thisp, val, i, this)) {
						res.push(val);
					}
				}
			}

			return res;
		};
	}

	// Extend jQuery
	(function($$){
		$$.fn.extend({
			annihilate: function() {
				if (this.length == 0) return;
				this.each( function(index) {
					var eachElement = $(this);
					eachElement.attr('id', 'oldElement_'+getRandomString(10));
					eachElement.removeClass();
					eachElement.detach();
					eachElement.empty();
				});
			}
		});
	})($);
})(window, $);
