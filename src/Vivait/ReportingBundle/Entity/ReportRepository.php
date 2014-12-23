<?php
/**
 * Created by PhpStorm.
 * User: kieljohn
 * Date: 10/12/14
 * Time: 16:52
 */

namespace Vivait\ReportingBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Vivait\ReportingBundle\Model\ReportingUserInterface;

class ReportRepository extends EntityRepository
{

    public function findAllByUser(ReportingUserInterface $user)
    {
        return $this->getEntityManager()->createQueryBuilder('r')
            ->select('r')
            ->from('VivaitReportingBundle:Report', 'r')
            ->join('r.shared_users', 'u')
            ->where('u.id = :uid')
            ->setParameter('uid', $user->getId())
            ->getQuery()
            ->getResult();
    }

}