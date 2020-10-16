<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms;

use Symfony\Component\Form\Extension\Core\Type\{
	ChoiceType, SubmitType, TextareaType
};
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\{Length, NotBlank};
use Symfony\Component\Form\{FormInterface, FormView, FormBuilderInterface};

/**
 * Class ResaltAnswerType3
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms
 */
class ResaltAnswerType3 extends ResaltAnswerBase {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$radios = [];
		$validateGroups = [];
		foreach ($options['formSettings'] as $key => $value) {
			$radios[ $value['title'] ] = $key;
			$validateGroups[] = "validate{$key}";
		}

		// Add radio buttons
		$builder->add('answerId', ChoiceType::class, [
			'choices'  => $radios,
			'expanded' => true,
			'multiple' => false,
			'mapped'   => false,
			'constraints' => [
				new NotBlank(['groups' => $validateGroups, 'message' => 'VALIDATOR_NOT_BLANK_RADIO'])
			],
		]);

		// Add fields for comments
		foreach ($options['formSettings'] as $key => $value) {
			$builder->add("comment{$key}", TextareaType::class, [
				'trim' => true,
				'label' => '',
				'mapped' => false,
				'required' => false,
				'attr' => [
					'placeholder' => $value['placeholder'],
					'data-radio-id' => ($key - 1),
				],
				'constraints' => [
					new NotBlank(['groups' => ["validate{$key}"], 'message' => 'VALIDATOR_NOT_BLANK_COMMENT']),
					new Length(['min' => 3, 'groups' => ["validate{$key}"], 'minMessage' => 'VALIDATOR_LENGTH_MIN']),
				],
			]);
		}

		$builder->add('save', SubmitType::class, [ 'label' => 'BTN_NEXT_QUESTION' ]);

		$builder->setDataMapper(new DataMappers\ResaltAnswerForm3DataMapper($options['formSettings']));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);

		// Add function for select validation group by answerId
		$resolver->setDefaults(['validation_groups' => function (FormInterface $form) {
			$selectedAnswerId = $form->get('answerId')->getData();
			return ['validate' . $selectedAnswerId];
		}]);
	}

	/**
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars['elementsCount'] = count($options['formSettings']) - 1;
	}
}