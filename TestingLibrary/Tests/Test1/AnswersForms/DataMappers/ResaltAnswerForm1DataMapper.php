<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms\DataMappers;

use Symfony\Component\Form\DataMapperInterface;

/**
 * Class ResaltAnswerForm1DataMapper
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms\DataMappers
 */
class ResaltAnswerForm1DataMapper implements DataMapperInterface {

	/**
	 * @var array
	 */
	private $_buttons;

	/**
	 * ResaltAnswerForm1DataMapper constructor.
	 * @param array $formButtons
	 */
	public function __construct(array $formButtons) {
		$this->_buttons = $formButtons;
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

		$data->setComment(null);
		$data->setAnswer('Value not specified');
		foreach ($this->_buttons as $btnKey => $btnValue) {
			if ($forms["answer{$btnKey}"]->isClicked()) {
				$data->setAnswerId((int) $btnKey);
				$data->setAnswer($btnValue);
				break;
			}
		}
	}
}