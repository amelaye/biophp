<?php

namespace Tests\MinitoolsBundle\Service;

use AppBundle\Service\Misc\MathematicsFunctions;
use MinitoolsBundle\Service\MicroarrayAnalysisAdaptiveManager;
use PHPUnit\Framework\TestCase;

class MicroarrayAnalysisAdaptiveManagerTest extends TestCase
{
    protected $mathematicsManager;

    public function setUp()
    {
        $this->mathematicsManager = new MathematicsFunctions();
    }

    public function testProcessMicroarrayDataAdaptiveQuantificationMethod()
    {
        $file = "Column\tRow\tName\t\tF532 Median\tB532 Median\tF635 Median\tB635 Median\r
1\t\t1\tControl -\t\t1145\t\t160\t\t\t1182\t\t122\r
2\t\t1\tControl -\t\t593\t\t\t218\t\t\t515\t\t\t122\r
3\t\t1\tControl -\t\t1257\t\t183\t\t\t1382\t\t128\r
4\t\t1\tControl -\t\t525\t\t\t168\t\t\t475\t\t\t126\r
5\t\t1\tControl -\t\t1132\t\t155\t\t\t1271\t\t120\r
6\t\t1\tControl -\t\t610\t\t\t218\t\t\t510\t\t\t122\r
7\t\t1\tControl -\t\t1099\t\t176\t\t\t1292\t\t127\r
8\t\t1\tControl -\t\t603\t\t\t180\t\t\t481\t\t\t123\r
9\t\t1\tControl -\t\t878\t\t\t149\t\t\t1082\t\t119\r
10\t\t1\tControl -\t\t441\t\t\t139\t\t\t444\t\t\t119\r
1\t\t2\tControl +\t10387\t\t140\t\t\t4269\t\t116\r
2\t\t2\tControl +\t9035\t\t132\t\t\t3705\t\t115\r
3\t\t2\tControl +\t7899\t\t126\t\t\t3331\t\t117\r
4\t\t2\tControl +\t7039\t\t118\t\t\t2883\t\t114\r
5\t\t2\tControl +\t9407\t\t138\t\t\t3994\t\t115\r
6\t\t2\tControl +\t7545\t\t127\t\t\t3240\t\t116\r
7\t\t2\tControl +\t8915\t\t134\t\t\t3843\t\t114\r
8\t\t2\tControl +\t7169\t\t126\t\t\t3038\t\t119\r
9\t\t2\tControl +\t7867\t\t137\t\t\t3345\t\t120\r
10\t\t2\tControl +\t9369\t\t140\t\t\t4184\t\t122\r
1\t\t3\tGene 1\t\t4276\t\t111\t\t\t574\t\t\t108\r
2\t\t3\tGene 1\t\t3798\t\t111\t\t\t439\t\t\t107\r
3\t\t3\tGene 1\t\t3311\t\t110\t\t\t418\t\t\t107\r
4\t\t3\tGene 1\t\t4258\t\t109\t\t\t441\t\t\t106\r
5\t\t3\tGene 1\t\t3548\t\t109\t\t\t445\t\t\t104\r
6\t\t3\tGene 1\t\t3448\t\t108\t\t\t424\t\t\t101\r
7\t\t3\tGene 1\t\t3412\t\t107\t\t\t415\t\t\t105\r
8\t\t3\tGene 1\t\t3856\t\t106\t\t\t445\t\t\t107\r
9\t\t3\tGene 1\t\t3510\t\t116\t\t\t395\t\t\t111\r
10\t\t3\tGene 1\t\t3853\t\t108\t\t\t427\t\t\t109\r
1\t\t4\tGene 2\t\t4830\t\t119\t\t\t670\t\t\t107\r
2\t\t4\tGene 2\t\t5625\t\t101\t\t\t804\t\t\t103\r
3\t\t4\tGene 2\t\t5053\t\t118\t\t\t682\t\t\t105\r
4\t\t4\tGene 2\t\t5895\t\t106\t\t\t835\t\t\t105\r
5\t\t4\tGene 2\t\t5913\t\t102\t\t\t816\t\t\t105\r
6\t\t4\tGene 2\t\t5041\t\t103\t\t\t773\t\t\t103\r
7\t\t4\tGene 2\t\t4846\t\t114\t\t\t703\t\t\t106\r
8\t\t4\tGene 2\t\t5362\t\t107\t\t\t812\t\t\t108\r
9\t\t4\tGene 2\t\t4811\t\t117\t\t\t716\t\t\t104\r
10\t\t4\tGene 2\t\t4610\t\t109\t\t\t627\t\t\t108\r
1\t\t5\tGene 3\t\t431\t\t\t99\t\t\t427\t\t\t107\r
2\t\t5\tGene 3\t\t536\t\t\t90\t\t\t520\t\t\t105\r
3\t\t5\tGene 3\t\t528\t\t\t100\t\t\t475\t\t\t109\r
4\t\t5\tGene 3\t\t489\t\t\t92\t\t\t504\t\t\t105\r
5\t\t5\tGene 3\t\t509\t\t\t93\t\t\t508\t\t\t108\r
6\t\t5\tGene 3\t\t486\t\t\t92\t\t\t523\t\t\t107\r
7\t\t5\tGene 3\t\t605\t\t\t104\t\t\t574\t\t\t111\r
8\t\t5\tGene 3\t\t562\t\t\t97\t\t\t638\t\t\t109\r
9\t\t5\tGene 3\t\t591\t\t\t108\t\t\t577\t\t\t112\r
10\t\t5\tGene 3\t\t609\t\t\t101\t\t\t626\t\t\t110\r
1\t\t6\tGene 4\t\t30728\t\t202\t\t\t3353\t\t130\r
2\t\t6\tGene 4\t\t41199\t\t206\t\t\t4245\t\t131\r
3\t\t6\tGene 4\t\t22218\t\t206\t\t\t2434\t\t128\r
4\t\t6\tGene 4\t\t30179\t\t199\t\t\t3062\t\t122\r
5\t\t6\tGene 4\t\t26642\t\t170\t\t\t2525\t\t122\r
6\t\t6\tGene 4\t\t23061\t\t184\t\t\t2259\t\t119\r
7\t\t6\tGene 4\t\t29017\t\t183\t\t\t2782\t\t114\r
8\t\t6\tGene 4\t\t27071\t\t176\t\t\t2747\t\t116\r
9\t\t6\tGene 4\t\t22631\t\t164\t\t\t2345\t\t110\r
10\t\t6\tGene 4\t\t23668\t\t191\t\t\t2559\t\t113\r
1\t\t7\tGene 5\t\t3190\t\t103\t\t\t2135\t\t104\r
2\t\t7\tGene 5\t\t3294\t\t106\t\t\t2389\t\t100\r
3\t\t7\tGene 5\t\t2114\t\t106\t\t\t1769\t\t107\r
4\t\t7\tGene 5\t\t3029\t\t103\t\t\t2524\t\t105\r
5\t\t7\tGene 5\t\t3236\t\t105\t\t\t2237\t\t103\r
6\t\t7\tGene 5\t\t3296\t\t106\t\t\t2379\t\t104\r
7\t\t7\tGene 5\t\t3131\t\t117\t\t\t2416\t\t110\r
8\t\t7\tGene 5\t\t3261\t\t112\t\t\t2440\t\t108\r
9\t\t7\tGene 5\t\t2866\t\t111\t\t\t2469\t\t107\r
10\t\t7\tGene 5\t\t2621\t\t116\t\t\t2057\t\t111\r
1\t\t8\tGene 6\t\t3791\t\t111\t\t\t3077\t\t109\r
2\t\t8\tGene 6\t\t4054\t\t112\t\t\t3210\t\t107\r
3\t\t8\tGene 6\t\t3235\t\t115\t\t\t2362\t\t112\r
4\t\t8\tGene 6\t\t3874\t\t117\t\t\t3070\t\t105\r
5\t\t8\tGene 6\t\t4208\t\t101\t\t\t3399\t\t109\r
6\t\t8\tGene 6\t\t3283\t\t117\t\t\t2779\t\t108\r
7\t\t8\tGene 6\t\t3354\t\t109\t\t\t2403\t\t105\r
8\t\t8\tGene 6\t\t4139\t\t104\t\t\t3307\t\t108\r
9\t\t8\tGene 6\t\t2706\t\t108\t\t\t2046\t\t108\r
10\t\t8\tGene 6\t\t3027\t\t101\t\t\t2693\t\t105\r
1\t\t9\tGene 7\t\t979\t\t\t98\t\t\t805\t\t\t108\r
2\t\t9\tGene 7\t\t877\t\t\t95\t\t\t766\t\t\t106\r
3\t\t9\tGene 7\t\t877\t\t\t98\t\t\t798\t\t\t110\r
4\t\t9\tGene 7\t\t932\t\t\t94\t\t\t791\t\t\t105\r
5\t\t9\tGene 7\t\t941\t\t\t95\t\t\t873\t\t\t111\r
6\t\t9\tGene 7\t\t995\t\t\t96\t\t\t943\t\t\t110\r
7\t\t9\tGene 7\t\t967\t\t\t109\t\t\t861\t\t\t112\r
8\t\t9\tGene 7\t\t1073\t\t101\t\t\t926\t\t\t109\r
9\t\t9\tGene 7\t\t984\t\t\t109\t\t\t893\t\t\t111\r
10\t\t9\tGene 7\t\t976\t\t\t106\t\t\t901\t\t\t110\r
1\t\t10\tGene 8\t\t215\t\t\t194\t\t\t152\t\t\t124\r
2\t\t10\tGene 8\t\t212\t\t\t187\t\t\t126\t\t\t120\r
3\t\t10\tGene 8\t\t276\t\t\t201\t\t\t177\t\t\t131\r
4\t\t10\tGene 8\t\t205\t\t\t188\t\t\t139\t\t\t124\r
5\t\t10\tGene 8\t\t227\t\t\t186\t\t\t137\t\t\t121\r
6\t\t10\tGene 8\t\t223\t\t\t209\t\t\t137\t\t\t123\r
7\t\t10\tGene 8\t\t214\t\t\t182\t\t\t139\t\t\t116\r
8\t\t10\tGene 8\t\t189\t\t\t168\t\t\t129\t\t\t114\r
9\t\t10\tGene 8\t\t217\t\t\t167\t\t\t163\t\t\t115\r
10\t\t10\tGene 8\t\t247\t\t\t179\t\t\t156\t\t\t118
";

        $aExpected = [
            "Control +" => [
                "n_data" => 10,
                "median1" => 0.63703336450222,
                "medlog1" => -0.196,
                "median2" => 1.5697936758674,
                "medlog2" => 0.196,
            ],
            "Control -" => [
                "n_data" => 10,
                "median1" => 0.2461501424198,
                "medlog1" => -0.609,
                "median2" => 4.062561127247,
                "medlog2" => 0.609,
            ],
            "Gene 1" => [
                "n_data" => 10,
                "median1" => 2.8815188911974,
                "medlog1" => 0.46,
                "median2" => 0.34717696173871,
                "medlog2" => -0.459,
            ],
            "Gene 2" => [
                "n_data" => 10,
                "median1" => 2.1001420837149,
                "medlog1" => 0.322,
                "median2" => 0.47615828774933,
                "medlog2" => -0.322,
            ],
            "Gene 3" => [
                "n_data" => 10,
                "median1" => 0.27498837403448,
                "medlog1" => -0.561,
                "median2" => 3.6365186091143,
                "medlog2" => 0.561
            ],
            "Gene 4" => [
                "n_data" => 10,
                "median1" => 2.6820086202854,
                "medlog1" => 0.428,
                "median2" => 0.37287397962263,
                "medlog2" => -0.428
            ],
            "Gene 5" => [
                "n_data" => 10,
                "median1" => 0.35196125412475,
                "medlog1" => -0.454,
                "median2" => 2.8419770166528,
                "medlog2" => 0.454
            ],
            "Gene 6" => [
                "n_data" => 10,
                "median1" => 0.33488612369748,
                "medlog1" => -0.475,
                "median2" => 2.9861053160715,
                "medlog2" => 0.475
            ],
            "Gene 7" => [
                "n_data" => 10,
                "median1" => 0.30168778156766,
                "medlog1" => -0.52,
                "median2" => 3.314797452231,
                "medlog2" => 0.52
            ],
            "Gene 8" => [
                "n_data" => 10,
                "median1" => 0.36970073513872,
                "medlog1" => -0.432,
                "median2" => 2.7049167337839,
                "medlog2" => 0.432
            ]
        ];

        $service = new MicroarrayAnalysisAdaptiveManager($this->mathematicsManager);
        $testFunction = $service->processMicroarrayDataAdaptiveQuantificationMethod($file);

        $this->assertEquals($testFunction, $aExpected);
    }

    public function testProcessMicroarrayDataAdaptiveQuantificationMethodException()
    {
        $this->expectException(\Exception::class);
        $file = [];

        $service = new MicroarrayAnalysisAdaptiveManager($this->mathematicsManager);
        $service->processMicroarrayDataAdaptiveQuantificationMethod($file);
    }
}