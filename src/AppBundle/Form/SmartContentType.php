<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\Type\DropdownType;

class SmartContentType extends AbstractType {

	/**
	 *
	 * @param FormBuilderInterface $builder        	
	 * @param array $options        	
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('status', DropdownType::class, [
			'choices' => [
				'smartcontent.parsed' => 'parsed', 
				'smartcontent.queued' => 'queued', 
				'smartcontent.discarded' => 'discarded', 
				'smartcontent.error' => 'error'
			]
		])->add('content');
	}

	/**
	 *
	 * @param OptionsResolver $resolver        	
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\SmartContent'
        ));
    }
}

?>