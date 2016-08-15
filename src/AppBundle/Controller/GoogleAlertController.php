<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;
use AppBundle\Entity\GoogleAlert;
use AppBundle\Form\GoogleAlertType;
use AppBundle\Entity\SmartContent;
use AppBundle\Service\Access;

/**
 * GoogleAlert controller.
 *
 * @Route("/googlealert")
 */
class GoogleAlertController extends Controller {
	private $Access;

	private function paginate($dql, $page = 1) {
		$pgStep = $this->getParameter('pgStep');
		$paginator = new Paginator($dql);
		$paginator->getQuery()->setFirstResult($pgStep * ($page - 1))->setMaxResults($pgStep);
		return $paginator;
	}

	private function getByGoogleAlertId($googleAlertId) {
		$doctrine = $this->getDoctrine();
		$smartContentRepository = $doctrine->getRepository('AppBundle:SmartContent');
		return $smartContentRepository->findBy([
			'googleAlertId' => $googleAlertId
		]);
	}

	/**
	 * Deletes a GoogleAlert entity.
	 *
	 * @Route("/delete/{id}", name="googlealert_delete")
	 * @Method("GET")
	 */
	public function deleteAction(Request $request, GoogleAlert $googleAlert) {
		$this->Access = Access::getFromContainer($this->container);
		if (!$this->Access->validateUserId($googleAlert->getUserId())) {return $this->redirectToRoute('restricted');}
		$em = $this->getDoctrine()->getManager();
		$GoogleApi = new \GoogleApi();
		$GoogleApi->init($this->getParameter('googleId'), $this->getParameter('googlePass'));
		$response = $GoogleApi->login();
		$response = $GoogleApi->deleteAlert($response, $googleAlert->getGoogleAlertId());
		$smartContents = $this->getByGoogleAlertId($googleAlert->getGoogleAlertId());
		foreach ($smartContents as $smartContent)
			$em->remove($smartContent);
		$em->remove($googleAlert);
		$em->flush();
		return $this->redirectToRoute('googlealert_index');
	}

	/**
	 * Creates a new GoogleAlert entity.
	 *
	 * @Route("/new", name="googlealert_new")
	 * @Method({"GET", "POST"})
	 */
	public function newAction(Request $request) {
		$this->Access = Access::getFromContainer($this->container);
		if (!$this->Access->isLogged()) {return $this->redirectToRoute('restricted');}
		$user = $this->Access->getLogged();
		$googleAlert = new GoogleAlert();
		$googleAlert->setUserId($user->getId());
		$form = $this->createForm('AppBundle\Form\GoogleAlertType', $googleAlert);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$GoogleApi = new \GoogleApi();
			$GoogleApi->init($this->getParameter('googleId'), $this->getParameter('googlePass'));
			$response = $GoogleApi->login();
			$response = $GoogleApi->createAlert($response, [
				'keywords' => '"' . $googleAlert->getKeyword() . '" ?' . $googleAlert->getUserId() . '?', 
				'often' => $googleAlert->getOften(), 
				'lang' => $googleAlert->getLang(), 
				'country' => $googleAlert->getCountry()
			]);
			if (!empty($response[4][0][1])) {
				$googleAlert->setGoogleAlertId($response[4][0][1]);
				$em->persist($googleAlert);
			} else {}
			$em->flush();
			return $this->redirectToRoute('googlealert_index');
		}
		return $this->render('googlealert/new.html.twig', [
			'googleAlert' => $googleAlert, 
			'form' => $form->createView()
		]);
	}

	/**
	 * Lists all GoogleAlert entities.
	 *
	 * @Route("/{page}", name="googlealert_index")
	 * @Method("GET")
	 */
	public function indexAction($page = 1) {
		$this->Access = Access::getFromContainer($this->container);
		if (!$this->Access->isLogged()) {return $this->redirectToRoute('restricted');}
		$user = $this->Access->getLogged();
		$em = $this->getDoctrine()->getManager();
		$googleAlerts = $em->getRepository('AppBundle:GoogleAlert');
		$query = $googleAlerts->createQueryBuilder('ga')->where('ga.userId = :userId')->orderBy('ga.id', 'DESC')->setParameter('userId', $user->getId())->getQuery();
		$paginator = $this->paginate($query, $page);
		$url = $this->generateUrl('googlealert_index');
		return $this->render('googlealert/index.html.twig', [
			'googleAlerts' => $paginator->getIterator(), 
			'paginator' => $this->renderView('helper/paginator.twig', [
				'url' => $url, 
				'maxPages' => ceil($paginator->count() / $this->getParameter('pgStep')), 
				'thisPage' => $page
			])
		]);
	}
}

?>
