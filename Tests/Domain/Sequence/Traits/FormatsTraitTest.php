<?php
namespace Tests\Domain\Sequence\Traits;

use Amelaye\BioPHP\Domain\Sequence\Traits\FormatsTrait;
use PHPUnit\Framework\TestCase;

class FormatsTraitTest extends TestCase
{
    /**
     * @return object
     */
    private function makeTraitObject()
    {
        return new class {
            use FormatsTrait;
        };
    }

    public function testLeft()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("Hel", $object->left("Hello", 3));
    }

    public function testRight()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("llo", $object->right("Hello", 3));
    }

    public function testIntrimRemovesEverySpaceIncludingLeadingAndTrailing()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("Hello", $object->intrim("H e l l o "));
    }

    public function testGetmin()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals(1, $object->getmin(3, 1, 2));
        $this->assertEquals(2, $object->getmin(5, 2, 3));
        $this->assertEquals(2, $object->getmin(5, 3, 2));
    }

    public function testRemRight()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("Hel", $object->rem_right("Hello", 2));
    }

    public function testRemRightDefaultsToOneCharacter()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("Hell", $object->rem_right("Hello"));
    }

    public function testTrimElement()
    {
        $object = $this->makeTraitObject();
        $value = " test ";
        $object->trim_element($value, 0);
        $this->assertEquals("test", $value);
    }
}
