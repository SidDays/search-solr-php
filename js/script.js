$(document).ready(function () {

    $("#autocomplete").autocomplete({
        serviceUrl: "suggest.php?q=" + encodeURIComponent($('#autocomplete').val()),
        onSelect: function (suggestion) {
            alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
        }
    });
});