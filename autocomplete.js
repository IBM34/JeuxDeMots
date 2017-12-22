$(document).ready(function() {
    var availableTags;
    $.get("noeuds.txt", function(data) {
        availableTags = data.split(',');
         $( "#terme" ).autocomplete({source:availableTags})
     });
});
