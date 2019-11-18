<?php
/**
 * Class RestrictionEnzymeDigestType
 * Freely inspired by BioPHP's project biophp.org
 * Created 7 april 2019
 * Last modified 18 april 2019
 */
namespace MinitoolsBundle\Form;

use AppBundle\Api\Bioapi;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Form RestrictionEnzymeDigestType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEnzymeDigestType extends AbstractType
{
    /**
     * @var array
     */
    private $vendors;

    /**
     * RestrictionEnzymeDigestType constructor.
     * @param   Bioapi   $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->vendors = $bioapi->getVendors();
    }

    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $wre["Select"] = "";
        foreach($this->vendors as $key => $data) {
            $wre[$key] = $key;
        }

        $textSequence = "ACGTACGTACGTTAGCTAGCTAGCTAGC";

        $builder->add(
            'sequence',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'data'  => $textSequence,
                'label' => "Sequence :",
                'required' => true,
                'constraints' => [
                    new Length([
                        "max" => 1000000,
                        "maxMessage" => "The maximum length of input string accepted is {{ limit }} characters"
                    ])
                ]
            ]
        );

        $builder->add(
            'showcode',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show code "
            ]
        );

        $builder->add(
            'minimum',
            ChoiceType::class,
            [
                'choices' => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                    6 => 6,
                    7 => 7,
                    8 => 8
                ],
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ],
                'label' => "Minimum recognition size for each restriction enzyme"
            ]
        );

        $builder->add(
            'retype',
            ChoiceType::class,
            [
                'choices' => [
                    "All" => 0,
                    "Blunt ends" => 1,
                    "Overhang end" => 2
                ],
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ],
                'label' => "Type of restriction enzyme"
            ]
        );

        $builder->add(
            'wre',
            ChoiceType::class,
            [
                'choices' => $wre,
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ],
                'label' => "Only use this endonuclease",
                'required' => false,
            ]
        );

        $builder->add(
            'defined',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Only restriction enzymes with known bases (no N, R, Y ...)"
            ]
        );

        $builder->add(
            'IIb',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Include Type IIb restriction enzymes (Two cleaves per recognition sequence)"
            ]
        );

        $builder->add(
            'IIs',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Include Type IIs restriction enzymes (Non-palindromic and cleavage outside of the recognition site)"
            ]
        );

        $builder->add(
            'onlydiff',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show only endonucleases showing different restriction patterns for searched sequences."
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Get list of restriction enzymes",
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ]
        );
    }
}