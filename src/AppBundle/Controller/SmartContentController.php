<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use AppBundle\Entity\SmartContent;
use AppBundle\Entity\SmartContentFilter;
use AppBundle\Entity\Ajax;
use AppBundle\Service\Access;

/**
 * SmartContent controller.
 *
 * @Route("/smartcontent")
 */
class SmartContentController extends Controller {
	private $Access;

	private function paginate($dql, $page = 1) {
		$pgStep = $this->getParameter('pgStep');
		$paginator = new Paginator($dql);
		$paginator->getQuery()->setFirstResult($pgStep * ($page - 1))->setMaxResults($pgStep);
		return $paginator;
	}

	private function getByGoogleAlertId($googleAlertId) {
		$doctrine = $this->getDoctrine();
		$googleAlertRepository = $doctrine->getRepository('AppBundle:GoogleAlert');
		return $googleAlertRepository->findOneBy([
			'googleAlertId' => $googleAlertId
		]);
	}

	private function getFilteredQuery($googleAlertId, $filter) {
		$user = $this->Access->getLogged();
		$doctrine = $this->getDoctrine();
		$smartContentRepository = $doctrine->getRepository('AppBundle:SmartContent');
		$qBuilder = $smartContentRepository->createQueryBuilder('sc');
		$qBuilder->where('sc.googleAlertId like :googleAlertId')->andWhere('sc.userId = :userId')->orderBy('sc.id', 'DESC')->setParameter('googleAlertId', $googleAlertId)->setParameter('userId', $user->getId());
		if (!empty($filter->getDateFrom())) {
			$qBuilder->andWhere('sc.created >= :dateFrom')->setParameter('dateFrom', $filter->getDateFrom());
		}
		if (!empty($filter->getDateTo())) {
			$qBuilder->andWhere('sc.created <= :dateTo')->setParameter('dateTo', $filter->getDateTo());
		}
		if (!empty($filter->getStatus())) {
			$qBuilder->andWhere('sc.status = :status')->setParameter('status', $filter->getStatus());
		}
		$query = $qBuilder->getQuery();
		return $query;
	}

	private function getSmartContentsByGoogleAlertId($googleAlertId, $page, $filter) {
		$query = $this->getFilteredQuery($googleAlertId, $filter);
		$paginator = $this->paginate($query, $page);
		return $paginator;
	}

	private function massiveDelete($googleAlertId, $filter) {
		$query = $this->getFilteredQuery($googleAlertId, $filter);
		$smartContents = $query->getResult();
		$em = $this->getDoctrine()->getManager();
		foreach ($smartContents as $smartContent) {
			$em->remove($smartContent);
		}
		$response = [];
		try {
			$em->flush();
			$response['status'] = Ajax::STATUS_OK;
			$response['msg'] = $this->get('translator')->trans('smartcontent.massiveDeleteOk', [], 'AppBundle');
			$response['callback'] = 'location.reload();';
		} catch (\Exception $e) {
			$response['status'] = Ajax::STATUS_FAIL;
			$response['msg'] = $this->get('translator')->trans('smartcontent.massiveDeleteFail', [], 'AppBundle');
		}
		return $response;
	}

	/**
	 * Displays a form to edit an existing SmartContent entity.
	 *
	 * @Route("/edit/{id}", name="smartcontent_edit")
	 * @Method({"GET", "POST"})
	 */
	public function editAction(Request $request, SmartContent $smartContent) {
		$this->Access = Access::getFromContainer($this->container);
		if (!$this->Access->validateUserId($smartContent->getUserId())) {return $this->redirectToRoute('restricted');}
		$editForm = $this->createForm('AppBundle\Form\SmartContentType', $smartContent);
		$editForm->handleRequest($request);
		if ($editForm->isSubmitted() && $editForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($smartContent);
			$em->flush();
			return $this->redirectToRoute('smartcontent_index', [
				'googleAlertId' => $smartContent->getGoogleAlertId()
			]);
		}
		return $this->render('smartcontent/edit.html.twig', [
			'smartContent' => $smartContent, 
			'form' => $editForm->createView()
		]);
	}

	/**
	 * Deletes a SmartContent entity.
	 *
	 * @Route("/delete/{id}", name="smartcontent_delete")
	 * @Method("GET")
	 */
	public function deleteAction(Request $request, SmartContent $smartContent) {
		$this->Access = Access::getFromContainer($this->container);
		if (!$this->Access->validateUserId($smartContent->getUserId())) {return $this->redirectToRoute('restricted');}
		$em = $this->getDoctrine()->getManager();
		$em->remove($smartContent);
		$em->flush();
		return $this->redirectToRoute('smartcontent_index', [
			'googleAlertId' => $smartContent->getGoogleAlertId()
		]);
	}

	/**
	 * Massive action SmartContent entities.
	 *
	 * @Route("/massive", name="smartcontent_massive_action")
	 * @Method("POST")
	 */
	public function massiveAction(Request $request) {
		$this->Access = Access::getFromContainer($this->container);
		if (!$this->Access->isLogged()) {return $this->redirectToRoute('restricted');}
		$response = null;
		if ($request->isXmlHttpRequest()) {
			switch ($request->request->get('action')) {
				case 'delete':
					{
						$googleAlertId = $request->request->get('googleAlertId');
						$filter = SmartContentFilter::fromJsonValue($request->request->get('jsonFilter'));
						$response = $this->massiveDelete($googleAlertId, $filter);
					}
				break;
			}
		}
		return new Response(json_encode($response));
	}

	/**
	 * Lists all SmartContent entities.
	 *
	 * @Route("/{googleAlertId}/{page}/{jsonFilter}", name="smartcontent_index")
	 * @Method({"GET", "POST"})
	 */
	public function indexAction($googleAlertId, $page = 1, $jsonFilter = '', Request $request) {
		$this->Access = Access::getFromContainer($this->container);
		$googleAlert = $this->getByGoogleAlertId($googleAlertId);
		if (!$this->Access->validateUserId($googleAlert->getUserId())) {return $this->redirectToRoute('restricted');}
		$filter = empty($jsonFilter) ? new SmartContentFilter() : SmartContentFilter::fromJsonValue($jsonFilter);
		$filterForm = $this->createForm('AppBundle\Form\SmartContentFilterType', $filter);
		$filterForm->handleRequest($request);
		$smartContents = $this->getSmartContentsByGoogleAlertId($googleAlertId, $page, $filter);
		$url = $this->generateUrl('smartcontent_index', [
			'googleAlertId' => $googleAlertId
		]);
		return $this->render('smartcontent/index.html.twig', [
			'smartContents' => $smartContents->getIterator(),
				'form' => $filterForm->createView(),
				'googleAlertId' => $googleAlertId,
				'jsonFilter' => $filter->toJsonValue(),
				'paginator' => $this->renderView(
					'helper/paginator.twig',
					[
						'url' => $url,
						'maxPages' => ceil($smartContents->count()/$this->getParameter('pgStep')),
						'thisPage' => $page,
						'jsonFilter' => $filter->toJsonValue()
					]
				),
			]
		);
	}

}

?>
