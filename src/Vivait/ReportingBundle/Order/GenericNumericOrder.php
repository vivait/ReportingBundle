<?php

namespace Vivait\ReportingBundle\Order;

use Symfony\Component\Form\AbstractType;
use Vivait\ReportingBundle\Form\Type\GenericOrderType;

class GenericNumericOrder extends ReportOrder
{

    public static function getAllChoices()
    {
        return [
            self::ORDER_BY_NONE => 'Off',
            self::ORDER_BY_ASC  => 'Low to High',
            self::ORDER_BY_DESC => 'High to Low',
        ];
    }

    /**
     * @return AbstractType
     */
    public function getFormType()
    {
        return new GenericOrderType(self::getAllChoices());
    }

}