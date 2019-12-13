<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\SubMatrix;
use PHPUnit\Framework\TestCase;

class SubMatrixTest extends TestCase
{
    public function testNewSubMatrix()
    {
        $oMatrix = new SubMatrix();
        $oMatrix->addrule("rule");

        $aRule = [0 => ["rule"]];
        $this->assertEquals($aRule, $oMatrix->getRules());
    }
}