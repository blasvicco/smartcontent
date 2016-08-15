<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\Type\DropdownType;

class GoogleAlertType extends AbstractType {

	/**
	 *
	 * @param FormBuilderInterface $builder        	
	 * @param array $options        	
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$CountryCode = new \CountryCode();
		$builder->add('keyword')->add('often', DropdownType::class, [
			'choices' => [
				'googlealert.asItHappens' => 'asItHappens', 
				'googlealert.onceADay' => 'onceADay', 
				'googlealert.onceAWeek' => 'onceAWeek'
			], 
			'data' => 'asItHappens'
		])->add('lang', DropdownType::class, [
			'choices' => [
				'googlealert.any' => 'en ', 
				'googlealert.english' => 'en', 
				'googlealert.spanish' => 'es'
			], 
			'data' => 'en '
		])->add('country', DropdownType::class, [
			'choices' => $CountryCode->getList(), 
			'data' => 'US'
		]);
	}

	/**
	 *
	 * @param OptionsResolver $resolver        	
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AppBundle\Entity\GoogleAlert'
		));
	}
}

?>