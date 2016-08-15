<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Form\Type\DropdownType;

class SmartContentFilterType extends AbstractType {

	/**
	 *
	 * @param FormBuilderInterface $builder        	
	 * @param array $options        	
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('dateFrom', DateType::class, [
			'format' => 'MM-dd-yyyy', 
			'widget' => 'single_text', 
			'required' => false, 
			'label' => 'smartcontent.dateFrom', 
			'translation_domain' => 'AppBundle'
		])->add('dateTo', DateType::class, [
			'format' => 'MM-dd-yyyy', 
			'widget' => 'single_text', 
			'required' => false, 
			'label' => 'smartcontent.dateTo', 
			'translation_domain' => 'AppBundle'
		])->add('status', DropdownType::class, [
			'choices' => [
				'smartcontent.parsed' => 'parsed', 
				'smartcontent.queued' => 'queued', 
				'smartcontent.discarded' => 'discarded', 
				'smartcontent.error' => 'error', 
				'smartcontent.any' => ''
			], 
			'label' => 'smartcontent.status', 
			'translation_domain' => 'AppBundle'
		])->add('smartcontent.clear', ButtonType::class, [
			'label' => 'smartcontent.clear', 
			'translation_domain' => 'AppBundle'
		])->add('smartcontent.search', SubmitType::class, [
			'label' => 'smartcontent.search', 
			'translation_domain' => 'AppBundle'
		]);
	}

	/**
	 *
	 * @param OptionsResolver $resolver        	
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AppBundle\Entity\SmartContentFilter'
		));
	}
}

?>
