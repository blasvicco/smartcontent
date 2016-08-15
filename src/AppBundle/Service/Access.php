<?php

namespace AppBundle\Service;

class Access {
	private static $instance = null;
	private $user = null;

	static function getFromContainer($container) {
		if (empty(self::$instance)) {
			self::$instance = new Access();
		}
		self::$instance->setLogged($container->get('security.token_storage')->getToken()->getUser());
		return self::$instance;
	}

	function setLogged($user) {
		$this->user = $user;
	}

	function getLogged() {
		return $this->user;
	}

	function isLogged() {
		return !!$this->user;
	}

	function validateUserId($userId) {
		return $this->isLogged() && ($this->user->getId() == $userId);
	}
}

?>