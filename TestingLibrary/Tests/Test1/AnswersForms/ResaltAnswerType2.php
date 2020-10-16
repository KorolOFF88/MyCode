<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\{Length, NotBlank};
use Symfony\Component\Form\Extension\Core\Type\{TextareaType, SubmitType};

/**
 * Class ResaltAnswerType2
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms
 */
class ResaltAnswerType2 extends ResaltAnswerBase {

	/**
	 * Create form with textarea comment
	 *
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// Add field for comment
		$builder->add('comment', TextareaType::class, [
			'trim' => true,
			'required' => true,
			'label' => $options['formSettings']['header'],
			'attr' => [ 'placeholder' => $options['formSettings']['placeholder'] ],
			'constraints' => [
				new NotBlank(['message' => 'VALIDATOR_NOT_BLANK_COMMENT']),
				new Length(['min' => 3, 'minMessage' => 'VALIDATOR_LENGTH_MIN']),
			],
		]);

		$builder->add('save', SubmitType::class, [ 'label' => 'BTN_NEXT_QUESTION' ]);
	}
}