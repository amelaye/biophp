<html>
<head><title>Microarray Data Analysis: adaptive quantification method</title></head>
<body bgcolor=FFFFFF>
<center>
    <H2>Microarray Data Analysis</H2>
    Spots are analyzed by the adaptive quantification method with local background subtractions
    <p>
        <?php
        // author    Joseba Bikandi
        // license   GNU GPL v2

        error_reporting(0);

        // IF NOTHING IS POSTED, PRINT FORM AND DIE
        if (!$_POST){print_form(); die();}              // THE FORM IS IN THE BOTTOM

        // IF DATA HAS BEEN POSTED, GET DATA AND PERFORM CALCULATIONS
        // get data (from textarea in the form)
        $data = $_POST["data"];

        // perform calculations
        $results=process_microarray_data_adaptive_quantification_method($data);


        // PRINT RESULTS TABLE
        print "<table border=1><tr><td>Identification</td><td>No. data</td><td>Channel 1</td><td>log10</td><td>Channel 2</td><td>log10</td></tr>";
        foreach($results as $key => $val){
            print "<tr><td>$key</td>";
            $median1=$results[$key]["median1"];
            $log1=round(log10($median1),3);
            $median1=round($median1,3);
            // define colors (red and blue)
            // red will mean over-expresion, and blue under-expresion
            $red1=255;$blue1=255;$green1=255;
            if ($log1<0){
                $red1=255-(-1)*round($log1*255);
                if($red1<0){$red1=0;}
                $green1=$red1;
            }elseif ($log1>0){
                $blue1=255-round($log1*255);
                if($blue1<0){$blue1=0;}
                $green1=$blue1;
            }
            print "<td>".$results[$key]["n_data"]."</td><td>$median1</td><td style=\"background-color: rgb($red1, $green1, $blue1);\">$log1</td>";
            $median2=$results[$key]["median2"];
            $log2=round(log10($median2),3);
            // definir color rojo y azul
            $red2=255;$blue2=255;$green2=255;
            if ($log2<0){
                $red2=255-(-1)*round($log2*255);
                if($red2<0){$red2=0;}
                $green2=$red2;
            }elseif ($log2>0){
                $blue2=255-round($log2*255);
                if($blue2<0){$blue2=0;}
                $green2=$blue2;
            }
            $median2=round($median2,3);
            print "<td>$median2</td><td style=\"background-color: rgb($red2, $green2, $blue2);\">$log2</td></tr>\n";
        }
        print "</table>";

        // END PRINT TABLE


        // ##########################################################################################################
        //                                       FUNCTIONS
        // ##########################################################################################################
        function process_microarray_data_adaptive_quantification_method($file){
            // find data for first column and row, and remove all headings;
            $file= substr($file,strpos($file,"1\t1\t"));
            // remove from file returns (\r) and (\")
            $file=preg_replace("/\r|\"/","",$file);

            // split file into lines ($data_array)
            $data_array=preg_split ("/\n/",$file, -1, PREG_SPLIT_NO_EMPTY);

            // compute data-background (save result in $data_array2) and
            //   sum of all data-background ($sum_ch1 and $sum_ch2)
            $sum_ch1=0;$sum_ch2=0;
            foreach($data_array as $key => $val){
                // example of line to be splitted:
                // 1        2        G16        1136        159        538        118
                // where        1 and 2 define position in the plate
                //              G16 is name of gene/experiment
                //              1136 is reading of chanel 1, and 159 is the background
                //              538 is reading of chanel 2, and 159 is the background
                $line_element=preg_split("/\t/",$val, -1, PREG_SPLIT_NO_EMPTY);
                if (sizeof ($line_element)<7){continue;}

                // This is the name of the gene studied
                $name=$line_element[2];

                // For chanel 1
                // calculate data obtained in chanel 1 minus background
                $ch1_bg=$line_element[3]-$line_element[4];
                // save data to a element in $data_array2 (separate diferent calculations from the same gene with commas)
                $data_array2[$name][1].=",".$ch1_bg;
                // $sum_ch1 will record the sum of all (chanel 1 - background) values
                $sum_ch1+=$ch1_bg;

                // For chanel 2
                // calculate data obtained in chanel 2 minus background
                $ch2_bg=$line_element[5]-$line_element[6];
                // save data to a element in $data_array2 (separate diferent calculations from the same gene with commas)
                $data_array2[$name][2].=",".$ch2_bg;
                // $sum_ch1 will record the sum of all (chanel 2 - background) values
                $sum_ch2+=$ch2_bg;

                // count number of total elements
                $n_data++;
            }

            // Compute (data-background)*100/sum(data-background)),
            //    where sum(data-background) is $sum_ch1 or $sum_ch2
            //    and save data in  $data_array3
            foreach($data_array2 as $key => $val){
                // split data separated by comma (chanel 1)
                $data_element=preg_split("/,/",$data_array2[$key][1], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($data_element as $key2 => $value){
                    // compute ratios
                    $ratio=$value*100/$sum_ch1;
                    // save result to $data_array3
                    $data_array3[$key][1].=",$ratio";
                }

                // split data separated by comma (chanel 2)
                $data_element=preg_split("/,/",$data_array2[$key][2], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($data_element as $key2 => $value){
                    // compute ratios
                    $ratio=$value*100/$sum_ch2;
                    // save result to $data_array3
                    $data_array3[$key][2].=",$ratio";
                }
            }

            // Compute ratios for values in chanel 1 and chanel 2
            //     chanel 1/chanel 2  and  chanel 2/chanel 1
            // save results to $data_array4
            foreach($data_array3 as $key => $val){

                $data_element1=preg_split("/,/",$data_array3[$key][1], -1, PREG_SPLIT_NO_EMPTY);
                $data_element2=preg_split("/,/",$data_array3[$key][2], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($data_element1 as $key2 => $value){
                    //compute ch1/ch2
                    $ratio=$data_element1[$key2]/$data_element2[$key2];
                    // and save
                    $data_array4[$key][1].=",$ratio";
                    //compute ch2/ch1
                    $ratio=$data_element2[$key2]/$data_element1[$key2];
                    // and save
                    $data_array4[$key][2].=",$ratio";
                }
            }

            ksort($data_array4);

            foreach($data_array4 as $key => $val){
                $results[$key]["n_data"]=substr_count($data_array4[$key][1],",");
                $results[$key]["median1"]=median($data_array4[$key][1]);
                $results[$key]["median2"]=median($data_array4[$key][2]);

            }
            return $results;
        }
        // ##########################################################################################################
        function mean($cadena) {
            $data=preg_split("/,/",$cadena,-1,PREG_SPLIT_NO_EMPTY);
            $sum = 0;
            $numValidElements = 0;

            foreach($data as $key => $val) {
                if(isset($val)) {
                    $sum += $val;
                    $numValidElements += 1;
                }
            }
            $mean = $sum / $numValidElements;
            $mean =round ($mean,3);
            return $mean;
        }
        // ##########################################################################################################
        function median($cadena) {
            $data=preg_split("/,/",$cadena,-1,PREG_SPLIT_NO_EMPTY);
            sort($data);
            $i=floor(sizeof($data)/2);
            if (sizeof($data)/2!=$i){
                return $data[$i];
            }
            return ($data[$i-1]+$data[$i])/2;
        }
        // ##########################################################################################################
        function variance($cadena) {
            $mean=mean($cadena);
            $data=preg_split("/,/",$cadena,-1,PREG_SPLIT_NO_EMPTY);
            $sum = 0;
            $numValidElements = 0;

            foreach($data as $key => $val) {
                if(  isset($val)  ) {
                    $tmp = $val - $mean;
                    $sum += $tmp * $tmp;
                    $numValidElements += 1;
                }
            }

            $variance = $sum / ( $numValidElements - 1 );
            $variance=round($variance,3);
            return $variance;
        }
        // ##########################################################################################################
        function print_form(){
        ?>


    <form method='post' action="<? print $_SERVER["PHP_SELF"]; ?>">
        <table cellpadding=5 width=650 border=0 bgcolor=DDFFFF>
            <tr><td>
                    <B>Microarray data:</B>
                </td></tr>
            <tr><td>
               <textarea name=data cols=120 rows=15>
<? print "Column\tRow\tName\tF532 Median\tB532 Median\tF635 Median\tB635 Median
1\t1\tControl -\t1145\t160\t1182\t122
2\t1\tControl -\t593\t218\t515\t122
3\t1\tControl -\t1257\t183\t1382\t128
4\t1\tControl -\t525\t168\t475\t126
5\t1\tControl -\t1132\t155\t1271\t120
6\t1\tControl -\t610\t218\t510\t122
7\t1\tControl -\t1099\t176\t1292\t127
8\t1\tControl -\t603\t180\t481\t123
9\t1\tControl -\t878\t149\t1082\t119
10\t1\tControl -\t441\t139\t444\t119
1\t2\tControl +\t10387\t140\t4269\t116
2\t2\tControl +\t9035\t132\t3705\t115
3\t2\tControl +\t7899\t126\t3331\t117
4\t2\tControl +\t7039\t118\t2883\t114
5\t2\tControl +\t9407\t138\t3994\t115
6\t2\tControl +\t7545\t127\t3240\t116
7\t2\tControl +\t8915\t134\t3843\t114
8\t2\tControl +\t7169\t126\t3038\t119
9\t2\tControl +\t7867\t137\t3345\t120
10\t2\tControl +\t9369\t140\t4184\t122
1\t3\tGene 1\t4276\t111\t574\t108
2\t3\tGene 1\t3798\t111\t439\t107
3\t3\tGene 1\t3311\t110\t418\t107
4\t3\tGene 1\t4258\t109\t441\t106
5\t3\tGene 1\t3548\t109\t445\t104
6\t3\tGene 1\t3448\t108\t424\t101
7\t3\tGene 1\t3412\t107\t415\t105
8\t3\tGene 1\t3856\t106\t445\t107
9\t3\tGene 1\t3510\t116\t395\t111
10\t3\tGene 1\t3853\t108\t427\t109
1\t4\tGene 2\t4830\t119\t670\t107
2\t4\tGene 2\t5625\t101\t804\t103
3\t4\tGene 2\t5053\t118\t682\t105
4\t4\tGene 2\t5895\t106\t835\t105
5\t4\tGene 2\t5913\t102\t816\t105
6\t4\tGene 2\t5041\t103\t773\t103
7\t4\tGene 2\t4846\t114\t703\t106
8\t4\tGene 2\t5362\t107\t812\t108
9\t4\tGene 2\t4811\t117\t716\t104
10\t4\tGene 2\t4610\t109\t627\t108
1\t5\tGene 3\t431\t99\t427\t107
2\t5\tGene 3\t536\t90\t520\t105
3\t5\tGene 3\t528\t100\t475\t109
4\t5\tGene 3\t489\t92\t504\t105
5\t5\tGene 3\t509\t93\t508\t108
6\t5\tGene 3\t486\t92\t523\t107
7\t5\tGene 3\t605\t104\t574\t111
8\t5\tGene 3\t562\t97\t638\t109
9\t5\tGene 3\t591\t108\t577\t112
10\t5\tGene 3\t609\t101\t626\t110
1\t6\tGene 4\t30728\t202\t3353\t130
2\t6\tGene 4\t41199\t206\t4245\t131
3\t6\tGene 4\t22218\t206\t2434\t128
4\t6\tGene 4\t30179\t199\t3062\t122
5\t6\tGene 4\t26642\t170\t2525\t122
6\t6\tGene 4\t23061\t184\t2259\t119
7\t6\tGene 4\t29017\t183\t2782\t114
8\t6\tGene 4\t27071\t176\t2747\t116
9\t6\tGene 4\t22631\t164\t2345\t110
10\t6\tGene 4\t23668\t191\t2559\t113
1\t7\tGene 5\t3190\t103\t2135\t104
2\t7\tGene 5\t3294\t106\t2389\t100
3\t7\tGene 5\t2114\t106\t1769\t107
4\t7\tGene 5\t3029\t103\t2524\t105
5\t7\tGene 5\t3236\t105\t2237\t103
6\t7\tGene 5\t3296\t106\t2379\t104
7\t7\tGene 5\t3131\t117\t2416\t110
8\t7\tGene 5\t3261\t112\t2440\t108
9\t7\tGene 5\t2866\t111\t2469\t107
10\t7\tGene 5\t2621\t116\t2057\t111
1\t8\tGene 6\t3791\t111\t3077\t109
2\t8\tGene 6\t4054\t112\t3210\t107
3\t8\tGene 6\t3235\t115\t2362\t112
4\t8\tGene 6\t3874\t117\t3070\t105
5\t8\tGene 6\t4208\t101\t3399\t109
6\t8\tGene 6\t3283\t117\t2779\t108
7\t8\tGene 6\t3354\t109\t2403\t105
8\t8\tGene 6\t4139\t104\t3307\t108
9\t8\tGene 6\t2706\t108\t2046\t108
10\t8\tGene 6\t3027\t101\t2693\t105
1\t9\tGene 7\t979\t98\t805\t108
2\t9\tGene 7\t877\t95\t766\t106
3\t9\tGene 7\t877\t98\t798\t110
4\t9\tGene 7\t932\t94\t791\t105
5\t9\tGene 7\t941\t95\t873\t111
6\t9\tGene 7\t995\t96\t943\t110
7\t9\tGene 7\t967\t109\t861\t112
8\t9\tGene 7\t1073\t101\t926\t109
9\t9\tGene 7\t984\t109\t893\t111
10\t9\tGene 7\t976\t106\t901\t110
1\t10\tGene 8\t215\t194\t152\t124
2\t10\tGene 8\t212\t187\t126\t120
3\t10\tGene 8\t276\t201\t177\t131
4\t10\tGene 8\t205\t188\t139\t124
5\t10\tGene 8\t227\t186\t137\t121
6\t10\tGene 8\t223\t209\t137\t123
7\t10\tGene 8\t214\t182\t139\t116
8\t10\tGene 8\t189\t168\t129\t114
9\t10\tGene 8\t217\t167\t163\t115
10\t10\tGene 8\t247\t179\t156\t118";
?>
</textarea>
                </td></tr>
            <tr><td align=center>
                    <input type='submit' value='Sutmit'>
                </td></tr>
        </table>
    </form>
    Copy tabulated data to the textarea as shown. First valid data must correspond to Column 1/Row 1.
    <br>All lines preceding data for Column 1/Row 1 will be removed previous to calculations.

    <hr>
    <table border=0><tr><td>
<pre><b>Computational procedure</b>:
  1. For each spot, the following is computed for both channels (1 and 2): (value-background)*100/sum(value-background))
  2. With previous results, ch1/ch2 and ch2/ch1 are computed
  3. As there are several replicas for ch1/ch2 and ch2/ch1, median and its base 10 logarithm is computed.
  4. In a table, medians and logs are represented. A color code is used to show over-expression (red) or under-expression (blue).
</pre>
            </td></tr></table>
    <hr>
    Source code available at <a href=http://www.biophp.org/minitools/microarray_analysis_adaptive_quantification>biophp.org</a>
</center>
</body>
</html>

<?
}
?>
<hr>
Source code available at <a href=http://www.biophp.org/minitools/microarray_analysis_adaptive_quantification>biophp.org</a>

