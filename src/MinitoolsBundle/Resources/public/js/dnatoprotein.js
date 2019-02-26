function Removeuseless(str) {
    str = str.split(/\d/).join("");
    str = str.split(/\W/).join("");
    return str;
}

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
    tidyup();
}

function tidyup() {
    str=document.mydna.sequence.value.toUpperCase();
    str=Removeuseless(str);

    if (!str) {document.mydna.sequence.value=''};
    var revstr=' ';
    var k=0;
    for (i =0; i<str.length; i++) {
        revstr+=str.charAt(i);
        k+=1;
        if (k==Math.floor(k/10)*10) {revstr+=' '};
        if (k==Math.floor(k/60)*60) {revstr+=k+'\n '};
    };

    document.mydna.sequence.value=revstr;
}

function Complement() {
    var str=document.mydna.sequence.value.toUpperCase();
    str = str.split("A").join("t");
    str = str.split("T").join("a");
    str = str.split("G").join("c");
    str = str.split("C").join("g");
    str=str.toUpperCase();
    document.mydna.sequence.value=str;
    tidyup();
};

function Clear() {
    document.mydna.sequence.value='';
};