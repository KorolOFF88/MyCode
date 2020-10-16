<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms\DataMappers;

use Symfony\Component\Form\DataMapperInterface;

/**
 * Class ResaltAnswerForm4DataMapper
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1\AnswersForms\DataMappers
 */
class ResaltAnswerForm4DataMapper implements DataMapperInterface {

	/**
	 * @var array
	 */
	private $_radios;

	/**
	 * ResaltAnswerForm4DataMapper constructor.
	 * @param array $_radios
	 */
	public function __construct(array $_radios) {
		$this->_radios = $_radios;
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

		$selectedAnswerId = intval($forms['answerId']->getData());
		$data->setAnswerId($selectedAnswerId);
		$data->setAnswer($this->_radios[$selectedAnswerId] ?? 'Value not specified');
		$data->setComment($forms['comment']->getData());
	}
}