<?php
/**
 * Reader for Fasta/Gff Entity
 * Freely inspired by BioPHP's project biophp.org
 * Created 1st september 2019
 * Last modified 1st september 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Fasta;
use AppBundle\Entity\Gff;
use AppBundle\Entity\Reader;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


/**
 * Class ReaderGffFastaManager
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ReaderGffFastaManager
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * ReaderGffFastaManager constructor.
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Reads file
     * @throws FileException
     */
    public function read()
    {
        if ($this->checkTypeValid() && $this->checkFileValid()) {
            if ($this->reader->getTypeFile() == "fasta") {
                $this->readFasta();
                $this->reader->setReadFile(true);
            }
            if ($this->reader->getTypeFile() == "gff") {
                $this->readGff();
                $this->reader->setReadFile(true);
            }
            $this->reader->setUid(array_unique($this->getUid()));
            $this->reader->setNumberUid(count($this->getUid()));
        }
    }

    /**
     * Return data ($id)
     * @param   int             $id
     * @return  Fasta | Gff
     */
    public function find($id)
    {
        if($this->reader->isReadFile()) {
            if ($this->reader->getType() == "fasta") {
                for ($i = 0; $i < $this->getNumberLines(); $i++) {
                    if (trim($this->reader->oObject[$i]->getId()) == trim($id)) {
                        return($this->reader->oObject[$i]);
                    }
                }
            }
            if ($this->reader->getType() == "gff") {
                for ($i=0;$i<$this->getNumberLines();$i++){
                    if (trim($this->reader->oObject[$i]->getSeqid()) == trim($id)){
                        return($this->reader->oObject[$i]);
                    }
                }
            }
            throw new FileException("Id ($id) not found! ");
        } else {
            throw new FileException("File not read");
        }
    }

    /**
     * Check if file is valid
     * @return  bool
     * @throws  FileException
     */
    private function checkFileValid()
    {
        if (is_file($this->reader->getInputFile())) {
            return true;
        } else {
            throw new FileException("Invalid file (".$this->reader->getInputFile().")!");
        }
    }

    /**
     * Check if type is valid
     * @return  bool
     * @throws  FileException
     */
    private function checkTypeValid()
    {
        if (strtolower($this->reader->getTypeFile()) == "gff" || strtolower($this->reader->getTypeFile()) == "fasta") {
            $this->reader->setTypeFile(strtolower($this->reader->getTypeFile()));
            return true;
        } else {
            throw new FileException("Invalid type (".$this->reader->getTypeFile().")!<br>");
        }
    }

    /**
     * Reads Fasta
     * @throws FileException
     */
    private function readFasta()
    {
        $this->checkTypeValid();
        $file = fopen($this->reader->getInputFile(),"r");
        $contSeq = 0;
        $cont = -1;

        while (!feof($file)) {
            $buffer = fgets($file);
            // read header
            if ($buffer[0] == ">") {
                $cont ++;
                $aux    = "";
                $all    = preg_split("/\s/",$buffer);
                $id     = str_replace(">","",$all[0]);
                $length = str_replace("length=","",$all[1]);
                $xy     = str_replace("xy=","",$all[2]);
                $region = str_replace("region=","",$all[3]);
                $run    = str_replace("run=","",$all[4]);
                $this->reader->oObject[$cont] = new Fasta();
                $this->reader->oObject[$cont]->setId($id);
                $this->reader->oObject[$cont]->setLength($length);
                $this->reader->oObject[$cont]->setXy($xy);
                $this->reader->oObject[$cont]->setRegion($region);
                $this->reader->oObject[$cont]->setRun($run);
                $this->reader->iUid[] = $id;
                $contSeq ++;

            } else { //read sequence
                $aux .= $buffer;
                $this->reader->oObject[$cont]->setSequence($aux);

            }
        }
        $this->reader->setNumberLines($contSeq);
    }

    /**
     * Read type GFF
     */
    private function readGff()
    {
        $this->checkTypeValid();
        $file = fopen($this->reader->getInputFile(),"r");
        $contSeq = 0;
        $cont = -1;

        while (!feof($file )) {
            $buffer = fgets($file);
            //read header
            if ($buffer[0]!="#") {
                $cont ++;
                $all = preg_split("/\t/",$buffer);
                $seqid = $all[0];
                if ($seqid != "") {
                    $source     = $all[1];
                    $type       = $all[2];
                    $start      = $all[3];
                    $end        = $all[4];
                    $score      = $all[5];
                    $strand     = $all[6];
                    $phase      = $all[7];
                    $attributes = $all[8];

                    $this->reader->oObject[$cont] = new gff();
                    $this->reader->oObject[$cont]->setSeqid($seqid);
                    $this->reader->oObject[$cont]->setSource($source);
                    $this->reader->oObject[$cont]->setType($type);
                    $this->reader->oObject[$cont]->setStart($start);
                    $this->reader->oObject[$cont]->setEnd($end);
                    $this->reader->oObject[$cont]->setScore($score);
                    $this->reader->oObject[$cont]->setStrand($strand);
                    $this->reader->oObject[$cont]->setPhase($phase);
                    $this->reader->oObject[$cont]->setAttributes($attributes);
                    $this->reader->oObject[] = $seqid;

                    $contSeq++;
                }
            }
        }
        $this->setNumberLines($contSeq);
    }
}