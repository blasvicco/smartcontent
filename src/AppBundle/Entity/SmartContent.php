<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SmartContent
 *
 * @ORM\Table(name="smart_content")
 * @ORM\Entity
 */
class SmartContent {
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
	 * @var string @ORM\Column(name="content", type="text", length=65535, nullable=false)
	 */
	private $content;
	/**
	 *
	 * @var \DateTime @ORM\Column(name="created", type="datetime", nullable=false)
	 */
	private $created;
	/**
	 *
	 * @var string @ORM\Column(name="url", type="string", length=255, nullable=false)
	 */
	private $url;
	/**
	 *
	 * @var string @ORM\Column(name="status", type="string", length=20, nullable=false)
	 */
	private $status;
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
	 * @return SmartContent
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
	 * Set content
	 *
	 * @param string $content        	
	 *
	 * @return SmartContent
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Set created
	 *
	 * @param \DateTime $created        	
	 *
	 * @return SmartContent
	 */
	public function setCreated($created) {
		$this->created = $created;
		return $this;
	}

	/**
	 * Get created
	 *
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * Set url
	 *
	 * @param string $url        	
	 *
	 * @return SmartContent
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}

	/**
	 * Get url
	 *
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
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