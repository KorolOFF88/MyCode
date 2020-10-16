<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms;

use Symfony\Component\OptionsResolver\OptionsResolver;
use wideweb\VKBundle\Entity\ResaltAnswer;
use Symfony\Component\Form\AbstractType;

/**
 * Class ResaltAnswerBase
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms
 */
abstract class ResaltAnswerBase extends AbstractType {

	/**
	 * Form name
	 */
	public const FORM_NAME = 'ResaltAnswer';

	/**
	 * Set form name
	 * @return string
	 */
	public function getBlockPrefix() {
		return self::FORM_NAME;
	}

	/**
	 * Configure input params for form creating
	 *
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => ResaltAnswer::class,
			'translation_domain' => 'TestsProcessing_Resalt',
		]);

		// Set required input parameters
		$resolver->setRequired(['formSettings']);
	}
}