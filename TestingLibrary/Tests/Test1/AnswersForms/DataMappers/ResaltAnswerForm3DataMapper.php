<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms\DataMappers;

use Symfony\Component\Form\DataMapperInterface;

/**
 * Class ResaltAnswerForm3DataMapper
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms\DataMappers
 */
class ResaltAnswerForm3DataMapper implements DataMapperInterface {

	/**
	 * @var array
	 */
	private $_formSettings;

	/**
	 * ResaltAnswerForm3DataMapper constructor.
	 * @param array $settings
	 */
	public function __construct(array $settings) {
		$this->_formSettings = $settings;
	}

	/**
	 * @param mixed $data
	 * @param \Symfony\Component\Form\FormInterface[]|\Traversable $forms
	 */
	public function mapDataToForms($data, $forms) {}

	/**
	 * @param \Symfony\Component\Form\FormInterface[]|\Traversable $forms
	 * @param mixed $data
	 */
	public function mapFormsToData($forms, &$data) {
		$forms = iterator_to_array($forms);

		$selectedAnswerId = $forms['answerId']->getData();
		$data->setAnswerId((int) $selectedAnswerId);
		$data->setAnswer($this->_formSettings[$selectedAnswerId]['title']);
		$data->setComment(
			is_null($forms['comment' . $selectedAnswerId])
				? 'NOT_ANSWER' : $forms['comment' . $selectedAnswerId]->getData()
		);
	}
}