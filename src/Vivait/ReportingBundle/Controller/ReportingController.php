<?php

namespace Vivait\ReportingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Vivait\BootstrapBundle\Controller\Controller;
use Vivait\ReportingBundle\Entity\Report;
use Vivait\ReportingBundle\Report\ReportBuilder;

class ReportingController extends Controller
{
    /**
     * Private function used to get a report from the service and load in entity properties if necessary
     * Also inject any dependancies into the filters/groups
     * @param $report_service_name
     * @param Report $report
     * @return ReportBuilder
     */
    private function getReport($report_service_name, Report $report = null)
    {
        $report_obj = clone $this->get($report_service_name);

        if (!$report_obj) {
            throw $this->createNotFoundException('The report does not exist');
        }

        if ($report) {
            /**
             * Pull the filters from the DB and apply them to the reportbuilder
             *
             * at this stage we have a list of filter/group objects in the reportbuilder and we
             * need to overwrite certain properties of those objects with ones that are attached
             * to the report entity in the database.
             *
             * We can't just overwrite the entire list of objects because we would lose critical
             * information as only a subset of this is stored when the object is serialised.
             */
            #replace this with a registry of 'requires'
            foreach ($report_obj->getFilters() as $key => &$row) {
                $row->injectReport(clone $report_obj);

                if ($report->getFilter($key)) {
                    $row->unserialize($report->getFilter($key)->serialize());
                }

                if ($report->getParent() && $row->getLinked()) {
                    $row->unserialize($report->getParent()->getFilter($key)->serialize());
                    $row->setLinked(true);
                }
            }

            foreach ($report_obj->getGroups() as $key => &$row) {
                $row->injectReport($report_obj);
                if ($report->getGroup($key)) {
                    $row->unserialize($report->getGroup($key)->serialize());
                }
            }

            foreach ($report_obj->getOrders() as $key => &$row) {
                $row->injectReport($report_obj);
                if ($report->getOrder($key)) {
                    $row->unserialize($report->getOrder($key)->serialize());
                }
            }

        }

        return $report_obj;
    }

    /**
     * Report Index
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $favourites = $this->getDoctrine()->getRepository('VivaitReportingBundle:Report')->findAllByUser($this->getUser());

        $reports = $this->get('vivait_reporting')->getReports();

        return $this->render('VivaitReportingBundle:Default:index.html.twig', ['reports' => $reports, 'favourites' => $favourites]);
    }

    /**
     * Create a new report instance to be stored in the database
     * @param $report
     * @param Report $parent
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction($report, Report $parent = null)
    {
        $report_obj = $this->getReport($report);

        $em = $this->getDoctrine()->getManager();

        $report_instance = new Report();
        $report_instance->setReportService($report);
        $report_instance->setFilters($report_obj->getFilters());
        $report_instance->setGroups($report_obj->getGroups());

        $em->persist($report_instance);
        $em->flush();

        return $this->redirect($this->generateUrl('vivait_reporting_build', ['report' => $report_instance->getId()]));

    }

    /**
     * Create a new comparison report instance to be stored in the database
     * @param Report $report
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Report $parent
     */
    public function createcomparisonAction(Report $report)
    {
        $report_obj = $this->getReport($report->getReportService());

        $em = $this->getDoctrine()->getManager();

        $report_instance = new Report();
        $report_instance->setReportService($report->getReportService());
        $report_instance->setFilters($report_obj->getFilters());
        $report_instance->setGroups($report_obj->getGroups());

        $report_instance->setParent($report);

        $em->persist($report_instance);
        $em->flush();

        return $this->redirect($this->generateUrl('vivait_reporting_build', ['report' => $report_instance->getId()]));

    }

    /**
     * Build the report and display it
     * @param Report $report
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function buildAction(Report $report)
    {

        $report_obj = $this->getReport($report->getReportService(), $report);
        $data['values'] = $report_obj->getQuery();
        $data['mappings'] = $report_obj->getColumnMapping();
        $data['comparison_status'] = false;

        foreach ($report->getComparisons() as $comparison) {
            $comparison_obj = $this->getReport($comparison->getReportService(), $comparison);
            $data = $this->get('vivait_reporting')->compareData($data['values'], $data['mappings'], $comparison_obj->getQuery(), $comparison_obj->getColumnMapping());
        }

        return $this->render(
            'VivaitReportingBundle:Default:report.html.twig',
            [
                'name'              => $report->getName(),
                'id'                => $report->getId(),
                'report'            => $report_obj,
                'parent'            => $report->getParent(),
                'comparisons'       => $report->getComparisons(),
                'data'              => $data['values'],
                'columnmapping'     => $data['mappings'],
                'comparison_status' => $data['comparison_status'],
            ]
        );
    }

    /**
     * Adjust the filters set on a report
     * @param $report
     * @param $filter
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function filterAction(Report $report, $filter, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $report_obj = $this->getReport($report->getReportService(), $report);

        #pull the filter from the report service as that is where the depinj version will live
        $filter_obj = $report_obj->getFilter($filter);

        $form = $this->createForm($filter_obj->getFormType(), $filter_obj);

        $form->handleRequest($request);
        if ($form->isValid()) {

            #save the filter to the report entity
            $report->setFilter($filter, $filter_obj);

            $em->persist($report);
            $em->flush();

            return $this->redirectBack($request);
        }


        return $this->render(
            'VivaitBootstrapBundle:Default:form.html.twig',
            [
                'form' => [
                    'form'  => $form->createView(),
                    'title' => $filter_obj->getName(),
                ]
            ]
        );
    }

    /**
     * Adjust the groups set on a report
     * @param $report
     * @param $group
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupAction(Report $report, $group, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $report_obj = $this->getReport($report->getReportService(), $report);

        #pull the group from the report service as that is where the depinj version will live
        $group_obj = $report_obj->getGroup($group);

        $form = $this->createForm($group_obj->getFormType(), $group_obj);

        $form->handleRequest($request);
        if ($form->isValid()) {

            #save the group to the report entity
            $report->setGroup($group, $group_obj);

            $em->persist($report);
            $em->flush();

            return $this->redirectBack($request);
        }


        return $this->render(
            'VivaitBootstrapBundle:Default:form.html.twig',
            [
                'form' => [
                    'form'  => $form->createView(),
                    'title' => $group_obj->getName(),
                ]
            ]
        );
    }

    /**
     * Adjust the orders set on a report
     * @param $report
     * @param $order
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderAction(Report $report, $order, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $report_obj = $this->getReport($report->getReportService(), $report);

        #pull the order from the report service as that is where the depinj version will live
        $order_obj = $report_obj->getOrder($order);

        $form = $this->createForm($order_obj->getFormType(), $order_obj);

        $form->handleRequest($request);
        if ($form->isValid()) {

            #save the order to the report entity
            $report->setOrder($order, $order_obj);

            $em->persist($report);
            $em->flush();

            return $this->redirectBack($request);
        }


        return $this->render(
            'VivaitBootstrapBundle:Default:form.html.twig',
            [
                'form' => [
                    'form'  => $form->createView(),
                    'title' => $order_obj->getName(),
                ]
            ]
        );
    }

    /**
     * Store a favorite report for future use (also be able to remove it)
     * @param Report $report
     * @param Request $request
     * @param int $remove
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function favoriteAction(Report $report, Request $request, $remove = 0)
    {

        $em = $this->getDoctrine()->getManager();

        if ($remove) {
            $report->removeSharedUser($this->getUser());
            if (!count($report->getSharedUsers())) {
                $report->setName(null);
            }
            $em->persist($report);
            $em->flush();

            return $this->redirectBack($request);
        }

        $users = $this->getDoctrine()->getRepository('VivaAuthBundle:User')->findAllinTenant($this->getUser()->getCurrentTenant());
        $report->addSharedUser($this->getUser());

        $form = $this->createFormBuilder($report)
            ->add('name', 'text')
            ->add('shared_users', 'entity', ['class' => 'Vivait\ReportingBundle\Model\ReportingUserInterface', 'attr' => ['size' => 30], 'label' => 'Share With', 'multiple' => true, 'choices' => $users])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {

            $em->persist($report);
            $em->flush();

            return $this->redirectBack($request);
        }


        return $this->render(
            'VivaitBootstrapBundle:Default:form.html.twig',
            [
                'form' => [
                    'form'  => $form->createView(),
                    'title' => 'Save Report',
                ]
            ]
        );
    }

    /**
     * Links a comparison filter against it's parent
     * @param Request $request
     * @param Report $report
     * @param $filter
     * @param bool $unlink
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function linkComparisonFilterAction(Request $request, Report $report, $filter, $unlink = false)
    {
        $filter_obj = $report->getFilter($filter);
        $filter_obj->setLinked(!$unlink);
        $report->setFilter($filter, $filter_obj);

        $em = $this->getDoctrine()->getManager();
        $em->persist($report);
        $em->flush();

        return $this->redirectBack($request);
    }


    /**
     * @param Request $request
     * @param Report $report
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function nameReportAction(Request $request, Report $report)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($report)
            ->add('name', 'text')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {

            $em->persist($report);
            $em->flush();

            return $this->redirectBack($request);
        }


        return $this->render(
            'VivaitBootstrapBundle:Default:form.html.twig',
            [
                'form' => [
                    'form'  => $form->createView(),
                    'title' => 'Name Report',
                ]
            ]
        );
    }

    /**
     * @param Report $report
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function deleteReportAction(Report $report)
    {
        $em = $this->getDoctrine()->getManager();
        $parent_id = $report->getParent()->getId();
        $em->remove($report);
        $em->flush();

        return $this->redirect($this->generateUrl('vivait_reporting_build', ['report' => $parent_id]));
    }
}
