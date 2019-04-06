<?php
/**
* Entity used by form DistanceAmongSequences
* Freely inspired by BioPHP's project biophp.org
* Created 26 february 2019
* Last modified 26 february 2019
*/
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DistanceAmongSequences
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class DistanceAmongSequences
{
    /**
     * @Assert\Length(
     *      max = 2000000,
     *      maxMessage = "This service does not handle input requests longer than {{ max }} bp."
     * )
     * @var string
     */
    private $seq;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $len;

    /**
     * @return string
     */
    public function getSeq()
    {
        return $this->seq;
    }

    /**
     * @param string $seq
     */
    public function setSeq($seq)
    {
        $this->seq = $seq;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return int
     */
    public function getLen()
    {
        return $this->len;
    }

    /**
     * @param int $len
     */
    public function setLen($len)
    {
        $this->len = $len;
    }
}