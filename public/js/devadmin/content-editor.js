function addNewRow(n) {
	var newRow;

	if (typeof n != "number" || n < 1 || n > 50) n = 1;

	for (var i = 0; i < n; i++) {
		newRow = $('<tr class="data"></tr>');
		newRow.append($('<td class="key" data-original_content=""></td>'));
		for (var starbarId in starbars) {
			newRow.append($('<td class="content content_' + starbars[starbarId].id + '" data-original_content=""></td>'));
		};
		newRow.makeElementEditable();
		$('table').append(newRow);
	}
}

function htmlEncode(s) {
	if (typeof s == "unidentified" || typeof s == null) return "";
	return s.replace("<", "&lt;").replace(">", "&gt;");
}

function cellClicked(event) {
	var cell = $(event.target);
	if (!cell.is('td')) {
		cell = cell.parents('td');
	}
	var row = cell.parent();
	if (!row.hasClass('edit')) {
		row.makeElementEditable();
		$('input, textarea', cell).focus().select();
	}
}

function saveRowToServer(event) {
	event.preventDefault();
	event.stopPropagation();

	var row = event.data.row;

	if (row.data('saving')) return;

	row.data('saving', true);
	row.css('opacity', '0.2', 200);
	$('input, textarea', row).prop('disabled', true);

	var data = {};
	var inputElement;


	data.key_id = row.data('key_id');
	inputElement = $('.key input', row);
	data.key_title = inputElement.val();

	for (var i in starbars) {
		inputElement = $('.content_' + starbars[i].id + ' textarea', row);
		data['content_' + starbars[i].id] = inputElement.val();
	};

	ajaxOptions = {
		success: function(response) {
			if (response.status != "success") {
				saveFailed(row);
				return;
			}
			row.css('opacity', '', 100);
			row.removeClass('edit', 100);
			setTimeout(function(){row.replaceRow(response.data)}, 110);
		},
		error: function() {
			return saveFailed(row);
		},
		type: 'POST',
		dataType: 'json',
		data: data,
		url: '/api/content/save-starbar-content'
	};

	$.ajax(ajaxOptions);

	/* = {
		key_id: 456,
		key_title: 'Whatever',
		content_2: 'Snakkle 2',
		content_3: 'Movie 2',
		content_4: 'Machinima 2',
		content_5: 'Social 2'
	}*/

}


function saveFailed(row) {
	alert("Save failed. Please ensure that you have entered a unique key.");
	row.css('opacity', '', 200);
	row.data('saving', false);
	$('input, textarea', row).prop('disabled', false);
}


function resetRow(event) {
	event.preventDefault();
	event.stopPropagation();
	var row = event.data.row;
	row.resetElement();
}

$.fn.extend({
	makeElementEditable: function() {
		var element = this;
		var inputElement;

		if (element.is('tr')) { // we're in a row, call this function on all child tds and exit
			element.addClass('edit', 100, null, function() {
				element.children('td').each(function(i) {$(this).makeElementEditable();});
			});
			return;
		}

		if (element.hasClass('key')) {
			inputElement = $('<input type="text">');
		} else { // content
			inputElement = $('<textarea></textarea>');
		}

		inputElement.val(element.data('original_content'));
		element.html('');
		element.append(inputElement);

		if (element.hasClass('key')) {
			var saveLink = $('<a class="save" href="#">Save</a>');
			saveLink.click({row: element.parent()}, saveRowToServer);
			element.append(saveLink);
			var resetLink = $('<a class="reset" href="#">Cancel</a>');
			resetLink.click({row: element.parent()}, resetRow);
			element.append(resetLink);
		}
	},

	resetElement: function() {
		var element = this;
		var newDiv;

		if (element.is('tr')) { // we're in a row, call this function on all child tds and exit
			if (!element.data('key_id') || element.data('key_id') == '') { // this is a new record, so delete the row if reseting
				element.remove();
				return;
			}
			element.children('td').each(function(i) {$(this).resetElement();});
			element.removeClass('edit', 100, null, function() {
				element.setPreDimensions();
			});
			return;
		}

		var originalContent = element.data('original_content') || '';
		newDiv = $('<div></div>');
		newDiv.text(originalContent);
		element.html('').append(newDiv);
	},

	replaceRow: function(newData) {
		var row = this;
		var newRow = $('<tr class="data"></tr>');
		var newCell, newDiv;

		newRow.data('key_id', newData.key_id);

		newCell = $('<td class="key"></td>');
		newCell.data('original_content', newData.key_title);
		newCell.text(newData.key_title);
		newRow.append(newCell);

		for (var i in starbars) {
			newCell = $('<td class="content content_' + starbars[i].id + '"></td>');
			newDiv = $('<div></div>');
			newDiv.text(newData['content_' + starbars[i].id]);
			newCell.data('original_content', newData['content_' + starbars[i].id]);
			newCell.append(newDiv);
			newRow.append(newCell);
		};

		newRow.click(cellClicked);

		row.replaceWith(newRow);

		newRow.setPreDimensions();
	},

	setPreDimensions: function() {
		$('pre', this).each(function(index) {
			var $this = $(this);
			$this.width($this.parent().width());
			$this.height($this.parent().height());
		});
	}
});

$(document).ready(function () {
	var dataCells = $('tr.data td');
	dataCells.click(cellClicked);
});
