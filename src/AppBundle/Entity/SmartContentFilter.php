<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SmartContentFilter
 *
 * @ORM\Entity
 */
class SmartContentFilter {
	/**
	 *
	 * @var \DateTime @ORM\Column(name="date_from", type="datetime", nullable=false)
	 */
	private $dateFrom;
	/**
	 *
	 * @var \DateTime @ORM\Column(name="date_to", type="datetime", nullable=false)
	 */
	private $dateTo;
	/**
	 *
	 * @var string @ORM\Column(name="status", type="string", length=20, nullable=false)
	 */
	private $status;

	/**
	 * Set dateFrom
	 *
	 * @param \DateTime $dateFrom        	
	 *
	 * @return SmartContentFilter
	 */
	public function setDateFrom($dateFrom) {
		$this->dateFrom = $dateFrom;
		return $this;
	}

	/**
	 * Get dateFrom
	 *
	 * @return \DateTime
	 */
	public function getDateFrom() {
		return $this->dateFrom;
	}

	/**
	 * Set dateTo
	 *
	 * @param \DateTime $dateTo        	
	 *
	 * @return SmartContentFilter
	 */
	public function setDateTo($dateTo) {
		$this->dateTo = $dateTo;
		return $this;
	}

	/**
	 * Get dateTo
	 *
	 * @return \DateTime
	 */
	public function getDateTo() {
		return $this->dateTo;
	}

	/**
	 * Set status
	 *
	 * @param string $status        	
	 *
	 * @return SmartContent
	 */
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}

	/**
	 * Get status
	 *
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	public function toJsonValue() {
		$dateFrom = $this->getDateFrom();
		$dateFrom = !empty($dateFrom) ? $dateFrom->format('m-d-Y') : '';
		$dateTo = $this->getDateTo();
		$dateTo = !empty($dateTo) ? $dateTo->format('m-d-Y') : '';
		return json_encode([
			'dateFrom' => $dateFrom, 
			'dateTo' => $dateTo, 
			'status' => $this->getStatus()
		]);
	}

	static public function fromJsonValue($json) {
		$objFilter = json_decode($json);
		$me = new self();
		if (!empty($objFilter->dateFrom)) {
			$me->setDateFrom(\DateTime::createFromFormat('m-d-Y', $objFilter->dateFrom));
		}
		if (!empty($objFilter->dateTo)) {
			$me->setDateTo(\DateTime::createFromFormat('m-d-Y', $objFilter->dateTo));
		}
		$me->setStatus($objFilter->status);
		return $me;
	}
}

?>