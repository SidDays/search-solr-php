$(document).ready(function () {

    $.get("http://localhost:8983/solr/hw4/suggest?q=magic", function(data, status){
        // alert("Data: " + data + "\nStatus: " + status);
        console.log(data);
        var term = "magic";
        var tuples = data.suggest.suggest[term].suggestions;
        console.log(tuples);
        var suggestions = [];
        for(var i = 0; i < tuples.length; i++) {
            suggestions.push(tuples[i].term);
        }
        console.log(suggestions);
    });

    $('#autocomplete').autoComplete({
        minChars: 1,
        source: function(term, suggest){
            term = term.toLowerCase();
            var choices = ['ActionScript', 'AppleScript', 'Asp', 'Assembly', 'BASIC', 'Batch', 'C', 'C++', 'CSS', 'Clojure', 'COBOL', 'ColdFusion', 'Erlang', 'Fortran', 'Groovy', 'Haskell', 'HTML', 'Java', 'JavaScript', 'Lisp', 'Perl', 'PHP', 'PowerShell', 'Python', 'Ruby', 'Scala', 'Scheme', 'SQL', 'TeX', 'XML'];
            var suggestions = [];
            for (i=0;i<choices.length;i++)
                if (~choices[i].toLowerCase().indexOf(term)) suggestions.push(choices[i]);
            suggest(suggestions);
        }
    });
});