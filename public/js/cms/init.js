$SQ(document).ready(function() {
/**
 * @author Peter Connolly
 * @todo Rewrite abstractly, so that start_at and end_at are classes that can apply to any pair of dates
 *
 */

$SQ('#start_at').datetimepicker({
    onClose: function(dateText, inst) {
        var endDateTextBox = $SQ('#end_at');
        if (endDateTextBox.val() != '') {
            var testStartDate = new Date(dateText);
            var testEndDate = new Date(endDateTextBox.val());
            if (testStartDate > testEndDate)
                endDateTextBox.val(dateText);
        }
        else {
            endDateTextBox.val(dateText);
        }
    },
    onSelect: function (selectedDateTime){
        var start = $SQ(this).datetimepicker('getDate');
        $SQ('#end_at').datetimepicker('option', 'minDate', new Date(start.getTime()));
    }
});
$SQ('#end_at').datetimepicker({
    onClose: function(dateText, inst) {
        var startDateTextBox = $SQ('#start_at');
        if (startDateTextBox.val() != '') {
            var testStartDate = new Date(startDateTextBox.val());
            var testEndDate = new Date(dateText);
            if (testStartDate > testEndDate)
                startDateTextBox.val(dateText);
        }
        else {
            startDateTextBox.val(dateText);
        }
    },
    onSelect: function (selectedDateTime){
        var end = $SQ(this).datetimepicker('getDate');
        $SQ('#start_at').datetimepicker('option', 'maxDate', new Date(end.getTime()) );
    }
});


});