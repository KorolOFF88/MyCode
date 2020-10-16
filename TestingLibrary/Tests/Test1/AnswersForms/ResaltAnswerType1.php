<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\{FormInterface, FormView};
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class ResaltAnswerType1
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms
 */
class ResaltAnswerType1 extends ResaltAnswerBase {

	/**
	 * Create form with multiple answers buttons
	 *
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		// Add answer's buttons
		foreach ($options['formSettings'] as $btnKey => $btnItem) {
			$builder->add('answer' . $btnKey, SubmitType::class, [
				'label' => $btnItem,
				'attr'  => [ 'value' => $btnKey ],
			]);
		}

		$builder->setDataMapper(new DataMappers\ResaltAnswerForm1DataMapper($options['formSettings']));
	}

	/**
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars['buttonsKeys'] = array_keys($options['formSettings']);
	}
}