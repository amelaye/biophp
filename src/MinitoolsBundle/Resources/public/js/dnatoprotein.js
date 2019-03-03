/**
 * DNA To Protein JS Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 3 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */

/**
 * click on Tidy Up button
 */
$('#tidyup-button').click(function() {
    tidyUp();
});

/**
 * click on Reverse button
 */
$('#reverse-button').click(function() {
    strrev();
});

/**
 * Click on Complement button
 */
$('#complement-button').click(function() {
    complement();
});

/**
 * Click on Clear button
 */
$('#clear-button').click(function() {
    clear();
});

/**
 * Remove irrelevant characters
 * @param       string  str
 * @returns     string
 */
function removeUseless(str) {
    str = str.split(/\d/).join("");
    str = str.split(/\W/).join("");
    return str;
}


/**
 * Reverse characters
 */
function strrev() {
    str = $('#dna_to_protein_sequence').val();
    $('#dna_to_protein_sequence').val(str.toUpperCase());

    if (!str) {
        $('#dna_to_protein_sequence').val();
    }
    var revstr='';
    var k = 0;
    for (i = str.length-1; i >= 0; i--) {
        revstr += str.charAt(i);
        k += 1;
    }
    $('#dna_to_protein_sequence').val(revstr);
    tidyUp();
}


/**
 * Tidies characters
 */
function tidyUp() {
    str = $('#dna_to_protein_sequence').val();
    $('#dna_to_protein_sequence').val(str.toUpperCase());
    str = removeUseless(str);

    if (!str) {
        $('#dna_to_protein_sequence').val('');
    }
    var revstr = ' ';
    var k = 0;
    for (i = 0; i < str.length; i++) {
        revstr += str.charAt(i);
        k += 1;
        if(k == Math.floor(k / 10) * 10) {
            revstr += ' '
        };
        if(k == Math.floor(k / 60) * 60) {
            revstr += k+'\n '
        };
    };

    $('#dna_to_protein_sequence').val(revstr);
}


/**
 * Completes string
 */
function complement() {
    var str = $('#dna_to_protein_sequence').val();
    str = str.split("A").join("t");
    str = str.split("T").join("a");
    str = str.split("G").join("c");
    str = str.split("C").join("g");
    str = str.toUpperCase();
    $('#dna_to_protein_sequence').val(str);
    tidyUp();
};


/**
 * Clears the "sequence" field
 */
function clear() {
    $('#dna_to_protein_sequence').val('');
};