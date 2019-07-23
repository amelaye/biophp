<?php
/**
 * SequenceManipulation Form Inputs
 * Freely inspired by BioPHP's project biophp.org
 * Created 22 july 2019
 * Last modified 22 july 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Class SequenceManipulationType
 * @package MinitoolsBundle\Form
 * @author Amelie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class SequenceManipulationType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = "GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGG\r
        GAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGG\r
        AGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGCGGA\r
        GTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGAGT\r
        GAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTG\r";


        $builder->add(
            'seq',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 4,
                    'class' => "form-control"
                ],
                'data' => $data,
                'required' => true
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Submit",
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ]
        );
    }
}