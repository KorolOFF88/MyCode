<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\{Length, NotBlank};
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, SubmitType, TextareaType};

/**
 * Class ResaltAnswerType4
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms
 */
class ResaltAnswerType4 extends ResaltAnswerBase {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// Add radio buttons
		$builder->add('answerId', ChoiceType::class, [
			'choices'  => array_flip($options['formSettings']['radios']),
			'expanded' => true,
			'multiple' => false,
			'mapped'   => false,
			'constraints' => [
				new NotBlank(['message' => 'VALIDATOR_NOT_BLANK_RADIO'])
			],
		]);

		// Add field for comment
		$builder->add('comment', TextareaType::class, [
			'trim' => true,
			'required' => true,
			'label' => $options['formSettings']['text']['header'],
			'attr' => [ 'placeholder' => $options['formSettings']['text']['placeholder'] ],
			'constraints' => [
				new NotBlank(['message' => 'VALIDATOR_NOT_BLANK_COMMENT']),
				new Length(['min' => 3, 'minMessage' => 'VALIDATOR_LENGTH_MIN']),
			],
		]);

		$builder->add('save', SubmitType::class, [ 'label' => 'BTN_NEXT_QUESTION' ]);

		$builder->setDataMapper(new DataMappers\ResaltAnswerForm4DataMapper($options['formSettings']['radios']));
	}
}