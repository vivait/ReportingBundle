<?php

namespace Vivait\ReportingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Vivait\ReportingBundle\Filter\ReportFilter;
use Vivait\ReportingBundle\Group\ReportGroup;
use Vivait\ReportingBundle\Model\ReportingUserInterface;
use Vivait\ReportingBundle\Order\ReportOrder;

/**
 * Report
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Vivait\ReportingBundle\Entity\ReportRepository")
 */
class Report
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    private $updatedAt;

    /**
     * @var string
     * @ORM\Column(name="name", type="text", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(name="filters", type="array", nullable=true)
     */
    private $filters;

    /**
     * @ORM\Column(name="groups", type="array", nullable=true)
     */
    private $groups;

    /**
     * @ORM\Column(name="orders", type="array", nullable=true)
     */
    private $orders;

    /**
     * @ORM\Column(name="service", type="text", length=255)
     */
    private $report_service;

    /**
     * @var Report
     * @ORM\OneToMany(targetEntity="Report", mappedBy="parent")
     **/
    private $comparisons;

    /**
     * @var Report
     * @ORM\ManyToOne(targetEntity="Report", inversedBy="comparisons")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    private $parent;


    /**
     * @var ReportingUserInterface[]
     * @ORM\ManyToMany(targetEntity="Vivait\ReportingBundle\Model\ReportingUserInterface")
     * @ORM\JoinTable(name="report_shared_users",
     *      joinColumns={@ORM\JoinColumn(name="report_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    private $shared_users;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->filters = [];
        $this->comparisons = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Report
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getReportService()
    {
        return $this->report_service;
    }

    /**
     * @param mixed $report_service
     */
    public function setReportService($report_service)
    {
        $this->report_service = $report_service;
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return ReportFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $filter_name
     * @return null|ReportFilter
     */
    public function getFilter($filter_name)
    {
        if (isset($this->filters[$filter_name])) {
            return $this->filters[$filter_name];
        }

        return null;
    }

    public function setFilter($filter_name, ReportFilter $filter)
    {
        $filters = $this->getFilters();

        $filters[$filter_name] = $filter;
        $this->setFilters($filters);
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * @return ReportGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param $group_name
     * @return null|ReportGroup
     */
    public function getGroup($group_name)
    {
        if (isset($this->groups[$group_name])) {
            return $this->groups[$group_name];
        }

        return null;
    }

    public function setGroup($group_name, ReportGroup $group)
    {
        $groups = $this->getGroups();

        $groups[$group_name] = $group;
        $this->setGroups($groups);
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * @param $order_name
     * @return null|ReportOrder
     */
    public function getOrder($order_name)
    {
        if (isset($this->orders[$order_name])) {
            return $this->orders[$order_name];
        }

        return null;
    }

    public function setOrder($order_name, ReportOrder $order)
    {
        $orders = $this->getOrders();

        $orders[$order_name] = $order;
        $this->setOrders($orders);
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * Convoluted way of updating the internal property so doctrine can detect the change
     * @param ReportFilter[] $filters
     */
    public function setFilters($filters)
    {
        if (!empty($filters) && $filters === $this->filters) {
            reset($filters);
            $key = key($filters);
            $filters[$key] = clone $filters[$key];
        }
        $this->filters = $filters;
    }

    /**
     * Convoluted way of updating the internal property so doctrine can detect the change
     * @param ReportGroup[] $groups
     */
    public function setGroups($groups)
    {
        if (!empty($groups) && $groups === $this->groups) {
            reset($groups);
            $key = key($groups);
            $groups[$key] = clone $groups[$key];
        }
        $this->groups = $groups;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return ReportOrder[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param ReportOrder[] $orders
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
    }


    /**
     * Add sharedUser
     *
     * @param ReportingUserInterface $sharedUser
     *
     * @return Report
     */
    public function addSharedUser(ReportingUserInterface $sharedUser)
    {
        $this->shared_users[] = $sharedUser;

        return $this;
    }

    /**
     * Remove sharedUser
     *
     * @param ReportingUserInterface $sharedUser
     */
    public function removeSharedUser(ReportingUserInterface $sharedUser)
    {
        $this->shared_users->removeElement($sharedUser);
    }

    /**
     * Get sharedUsers
     *
     * @return ReportingUserInterface[]
     */
    public function getSharedUsers()
    {
        return $this->shared_users;
    }

    /**
     * Add comparison
     *
     * @param Report $comparison
     *
     * @return Report
     */
    public function addComparison(Report $comparison)
    {
        $this->comparisons[] = $comparison;

        return $this;
    }

    /**
     * Remove comparison
     *
     * @param Report $comparison
     */
    public function removeComparison(Report $comparison)
    {
        $this->comparisons->removeElement($comparison);
    }

    /**
     * Get comparisons
     *
     * @return Report[]
     */
    public function getComparisons()
    {
        return $this->comparisons;
    }

    /**
     * Set parent
     *
     * @param Report $parent
     *
     * @return Report
     */
    public function setParent(Report $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Report
     */
    public function getParent()
    {
        return $this->parent;
    }
}
