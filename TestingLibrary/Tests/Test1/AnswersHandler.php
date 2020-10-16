<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1;

use wideweb\VKBundle\Entity\{ResaltAnswer, ResaltQuestion};
use wideweb\VKBundle\Libraries\TestingLibrary\{
	Exceptions\TestCompletedException, ITestAnswersHandler, BaseAnswersHandler
};

/**
 * Class AnswersHandler
 * Обработчик ответов соискателя на вопросы теста Резалт
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1
 */
class AnswersHandler extends BaseAnswersHandler implements ITestAnswersHandler {
	/**
	 * Объект текущего вопроса
	 * @var ResaltQuestion|null
	 */
	private $_currentQuestion = null;

	/**
	 * Загрузка вопроса текущего шага
	 * @return ResaltQuestion
	 * @throws TestCompletedException
	 */
	protected function getCurrentQuestion(): ResaltQuestion {
		if (is_null($this->_currentQuestion)) {
			if (is_null($this->currentStep)) {
				throw new TestCompletedException($this->test);
			}

			$this->_currentQuestion = $this->entityManager->find(ResaltQuestion::class, $this->currentStep);
			if (is_null($this->_currentQuestion)) {
				throw new \Exception('Incorrect test\'s step');
			}
		}

		return $this->_currentQuestion;
	}

	/**
	 * Получение типа формы вопроса
	 * @return string
	 * @throws TestCompletedException
	 * @throws \Exception
	 */
	protected function getQuestionFormType(): string {
		$formType = '';
		switch ($this->getCurrentQuestion()->getAnswerType()) {
			case ResaltQuestion::ANSWER_TYPE_BUTTONS_LIST:
				$formType = AnswersForms\ResaltAnswerType1::class;
				break;
			case ResaltQuestion::ANSWER_TYPE_COMMENT:
				$formType = AnswersForms\ResaltAnswerType2::class;
				break;
			case ResaltQuestion::ANSWER_TYPE_RADIO_AND_COMMENT:
				$formType = AnswersForms\ResaltAnswerType3::class;
				break;
			case ResaltQuestion::ANSWER_TYPE_RADIOS_LIST:
				$formType = AnswersForms\ResaltAnswerType4::class;
				break;
			default:
				throw new \Exception('Undefined Resalt answer type.');
		}

		return $formType;
	}

	/**
	 * Получение объекта с данными для заполнения формы
	 * @return mixed|ResaltAnswer
	 * @throws TestCompletedException
	 */
	protected function getQuestionFormData() {
		return new ResaltAnswer($this->test->getParticipantObj(), $this->getCurrentQuestion());
	}

	/**
	 * Получение настроек формы
	 * @return array
	 * @throws TestCompletedException
	 */
	protected function getQuestionFormSettings(): array {
		return [ 'formSettings' => $this->getCurrentQuestion()->translate($this->locate)->getSettings() ];
	}

	/**
	 * Получение номера следующего вопроса
	 * если возвращается null - тест завершен
	 * @param ResaltAnswer $currentAnswer
	 * @return int|null
	 */
	protected function getNextStep($currentAnswer): ?int {
		$step = $this->currentStep;
		$tmpAnswerId = $currentAnswer->getAnswerId();
		if ($step === 5 and ($tmpAnswerId === 1 or $tmpAnswerId === 2)) {
			$step = 7; // Вопрос #5: ответ 1 или 2  - переход на 7 (иначе 6)
		} else if ($step === 7 and $tmpAnswerId === 3) {
			$step = 10; // Вопрос #7: ответ 3 - переход на 10 (иначе 8)
		} else if ($step === 8 and $tmpAnswerId === 3) {
			$step = 10; // Вопрос #8: ответ 3 - переход на 10 (иначе 9)
		} else if ($step === 12 and $tmpAnswerId === 2) {
			$step = 14; // Вопрос #12: ответ 2 - переход на 14 (иначе 13)
		} else if ($step === 22 and ($tmpAnswerId === 1 or $tmpAnswerId === 2)) {
			$step = 24; // Вопрос #22: ответ 1 или 2  - переход на 24 (иначе 23)
		} else if ($step === 24 and $tmpAnswerId === 3) {
			$step = 27; // Вопрос #24: ответ 3 - переход на 27 (иначе 25)
		} else if ($step === 25 and $tmpAnswerId === 3) {
			$step = 27; // Вопрос #25: ответ 3 - переход на 27 (иначе 26)
		} else if ($step === 29 and $tmpAnswerId === 2) {
			$step = 31; // Вопрос #29:  ответ 2 - переход на 31 (иначе 30)
		} else if (($step === 19 and $tmpAnswerId === 2) or $step === 31) {
			$step = null;   // Тест завершен!
		} else {
			$step++;
		}

		return $step;
	}

	/**
	 * Получение списка параметров для отрисовки Twig View,
	 * в котором отображается форма прохождения теста
	 * @return array
	 * @throws TestCompletedException
	 */
	public function getProcessingViewParams(): array {
		$params = parent::getProcessingViewParams();
		$params['question'] = $this->getCurrentQuestion();
		return $params;
	}
}