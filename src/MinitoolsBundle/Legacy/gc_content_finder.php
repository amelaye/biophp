# ========================================================
#
# Author : Renjith.R, Kerala
# license   GNU GPL v2
# Please Make a folder uploads in root
# This work is Done by Renjith.R, Research Officer, bioWORLD
#
#
# ========================================================
<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
    <p><strong>Upload Nucleotide Sequence in Fasta format to upload </strong></p>
    <p>Important: Please use only txt files The file should contain only Letters </p>
    <table>
        <tr>
            <td colspan="2"><input name="uploadedfile" type="file" /></td>
        <tr>
            <td width="137"><input type="hidden" name="MAX_FILE_SIZE" value="100000" />
                :

                <br /></td>
            <td width="79"><input name="submit" type="submit" value="Upload File" /></td>
    </table>
</form>
Source code is available at<a href=http://www.biophp.org/minitools/minitool_name>BioPHP.org</a>
<?php

echo "<br>";
function IsValidSequence($sequence)
{
    $length = strlen($sequence);
    for ($i=0;$i<$length;++$i)
    {
        if(	!($sequence[$i]=='a' || $sequence[$i]=='A'||
            $sequence[$i]=='t'|| $sequence[$i]=='T' ||
            $sequence[$i]=='g'|| $sequence[$i]=='G'||
            $sequence[$i]=='c'|| $sequence[$i]=='c'))
        {
            return false;
        }
        else return true;
    }

}
echo $_POST['uploadedfile'];
$target_path = "uploads/";

$target_path = $target_path . basename( $_FILES['uploadedfile']['name']);

if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
    echo "The file ".  basename( $_FILES['uploadedfile']['name']).
        " has been uploaded";
} else{
    echo " Please Upload the file";
}


$target_path = "uploads/";

$target_path = $target_path . basename( $_FILES['uploadedfile']['name']);

if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
    echo "The file ".  basename( $_FILES['uploadedfile']['name']).
        " has been uploaded";
}
echo "<br>";

mysql_connect('localhost','root','');
$myFile = "uploads/" . basename( $_FILES['uploadedfile']['name']) ;
$fh = fopen($myFile, 'r');
$var = fread($fh, 1000000);
//echo $var;
fclose($fh);


$length = strlen($var);
//echo $var[0];
//echo $var[0];
//echo "<br>";
if($length!='')
{

    if(IsValidSequence($var))
    {

//$a=0;$g=0;$t=0;$c=0;
        for ($i=0;$i<$length;++$i)
        {

            switch($var[$i])
            {
                case 'a':
                case 'A':
                    $a++;
                    break;
                case 't':
                case 'T':
                    $t++;
                    break;

                case 'c':
                case 'C':
                    $c++;
                    break;

                case 'g':
                case 'G':
                    $g++;

            }
        }
        echo "<br>";
        echo "Total Length of Sequence is $length";
        echo "<br>";
        echo "Number of A = $a";
        echo "<br>";
        echo "Number of  T= $t";
        echo "<br>";
        echo "Number of G = $g";
        echo "<br>";
        echo "Number of C= $c";
        echo"<br>";
        $at= (($a+$t)/$length)*100;
        echo "% of at =$at";
        echo"<br>";
        $gc= (($g+$c)/$length)*100;
        echo "% of gc =$gc";
    }
    else
        echo "Please check....This is not a Nucleotide sequense";
}
?>


