<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GoogleAlert
 *
 * @ORM\Table(name="google_alert")
 * @ORM\Entity
 */
class GoogleAlert {
	/**
	 *
	 * @var string @ORM\Column(name="google_alert_id", type="string", length=45, nullable=false)
	 */
	private $googleAlertId;
	/**
	 *
	 * @var integer @ORM\Column(name="user_id", type="integer", nullable=false)
	 */
	private $userId;
	/**
	 *
	 * @var string @ORM\Column(name="keyword", type="string", length=255, nullable=false)
	 */
	private $keyword;
	/**
	 *
	 * @var string @ORM\Column(name="often", type="string", length=20, nullable=false)
	 */
	private $often;
	/**
	 *
	 * @var string @ORM\Column(name="lang", type="string", length=2, nullable=false)
	 */
	private $lang;
	/**
	 *
	 * @var string @ORM\Column(name="country", type="string", length=2, nullable=false)
	 */
	private $country;
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer")
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * Set googleAlertId
	 *
	 * @param string $googleAlertId        	
	 *
	 * @return GoogleAlert
	 */
	public function setGoogleAlertId($googleAlertId) {
		$this->googleAlertId = $googleAlertId;
		return $this;
	}

	/**
	 * Get googleAlertId
	 *
	 * @return string
	 */
	public function getGoogleAlertId() {
		return $this->googleAlertId;
	}

	/**
	 * Set userId
	 *
	 * @param integer $userId        	
	 *
	 * @return GoogleAlert
	 */
	public function setUserId($userId) {
		$this->userId = $userId;
		return $this;
	}

	/**
	 * Get userId
	 *
	 * @return integer
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Set keyword
	 *
	 * @param string $keyword        	
	 *
	 * @return GoogleAlert
	 */
	public function setKeyword($keyword) {
		$this->keyword = $keyword;
		return $this;
	}

	/**
	 * Get keyword
	 *
	 * @return string
	 */
	public function getKeyword() {
		return $this->keyword;
	}

	/**
	 * Set often
	 *
	 * @param string $often        	
	 *
	 * @return GoogleAlert
	 */
	public function setOften($often) {
		$this->often = $often;
		return $this;
	}

	/**
	 * Get often
	 *
	 * @return string
	 */
	public function getOften() {
		return $this->often;
	}

	/**
	 * Set lang
	 *
	 * @param string $lang        	
	 *
	 * @return GoogleAlert
	 */
	public function setLang($lang) {
		$this->lang = $lang;
		return $this;
	}

	/**
	 * Get lang
	 *
	 * @return string
	 */
	public function getLang() {
		return $this->lang;
	}

	/**
	 * Set country
	 *
	 * @param string $country        	
	 *
	 * @return GoogleAlert
	 */
	public function setCountry($country) {
		$this->country = $country;
		return $this;
	}

	/**
	 * Get country
	 *
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}
}

?>