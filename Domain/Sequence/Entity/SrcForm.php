<?php
/**
 * Doctrine Entity SrcForm
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SrcForm
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "src_form")]
class SrcForm
{
    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private string $primAcc = "";

    /**
     * @var string
     */
    #[ORM\Column(type: "text")]
    private string $entry = "";

    /**
     * @return string
     */
    public function getPrimAcc() : string
    {
        return $this->primAcc;
    }

    /**
     * @param string $primAcc
     */
    public function setPrimAcc(string $primAcc) : void
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return string
     */
    public function getEntry() : string
    {
        return $this->entry;
    }

    /**
     * @param string $entry
     */
    public function setEntry(string $entry) : void
    {
        $this->entry = $entry;
    }
}