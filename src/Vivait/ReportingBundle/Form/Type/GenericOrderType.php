<?php
namespace Vivait\ReportingBundle\Form\Type;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Viva\BravoBundle\Report\Order\ApplicationsOrder;
use Vivait\ReportingBundle\Form\Type\ReportFilterType;

class GenericOrderType extends ReportFilterType
{
    /**
     * @var array
     */
    private $choices;

    /**
     * @param $choices
     */
    function __construct($choices) {
        $this->choices = $choices;
    }

    public function buildForm(FormBuilderInterface $form, array $options)
    {
        $form->add(
            'order',
            'choice',
            array(
                'label'    => 'Ordering',
                'required' => true,
                'choices' => $this->choices
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    public function getName()
    {
        return 'report_order_generic';
    }
}