<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DropdownType extends ChoiceType {

	public function getBlockPrefix() {
		return 'dropdown';
	}
}

?>