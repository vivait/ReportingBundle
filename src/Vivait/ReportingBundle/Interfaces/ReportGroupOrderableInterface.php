<?php

namespace Vivait\ReportingBundle\Interfaces;


interface ReportGroupOrderableInterface
{


//    /**
//     * Returns an array of all the choices that this element can be ordered by
//     * @return array
//     */
//    public static function getAllOrderChoices();

    /**
     * @return mixed
     */
    public function getOrder();


}