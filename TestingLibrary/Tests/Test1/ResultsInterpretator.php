<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1;

use wideweb\VKBundle\Entity\ResaltAnswer;
use wideweb\VKBundle\Libraries\TestingLibrary\{
	BaseResultsInterpretator,
	ITestResultsInterpreter,
	ITestResultsApiInterpreter,
	ITestResultsPdfInterpreter,
	TTestResultsInterpreter,
	TTestResultsPdfInterpreter
};

/**
 * Class ResultsInterpretator
 * Интерпретатор результатов теста Резалт
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1
 */
class ResultsInterpretator
	extends BaseResultsInterpretator
	implements ITestResultsInterpreter, ITestResultsPdfInterpreter, ITestResultsApiInterpreter {

	use TTestResultsInterpreter;
	use TTestResultsPdfInterpreter;

	/**
	 * @var string Префикс для имени PDF-файла
	 */
	protected $pdfFileNamePrefix = 'Resalt_';


	/**
	 * Формирование массива с данными для интерпретации
	 * @param array $additionalParams
	 * @return array
	 */
	private function _interpretation(array $additionalParams = []): array {
		// Проверка на первичный просмотр
		if ($this->isFirstViewing()) {
			// В дальнейшем здесь можно сделать генерацию события
			// первичного просмотра теста
		}

		$participant = $this->test->getParticipantObj();

		$vacancy = $participant->getVacancy();
		if (! is_null($vacancy)) {
			$this->locale = $vacancy->getEmailLang();
		}

		return [
			'locale'  => $this->locale,
			'vacancy' => [
				'id'    => is_null($vacancy) ? null : (int) $vacancy->getId(),
				'title' => is_null($vacancy) ? null : $vacancy->getName(),
			],
			'participant' => [
				'id'  => (int) $participant->getId(),
				'age' => (int) $participant->getAge(),
				'email'      => $participant->getEmail(),
				'status'     => $participant->getStatus(),
				'avgRating'  => $participant->getAvgRating(),
				'firstName'  => $participant->getName(),
				'secondName' => $participant->getLastName(),
				'ownerUserId'=> $participant->getCreator()->getId(),
			],
			'test' => [
				'id'         => (int) $this->test->getId(),
				'type'       => 'Resalt',
				// проверки на null для старых тестов, у которых эти даты могут быть не установленны
				'sendDate'   => is_null($this->test->getDateSendTest())   ? '' : $this->test->getDateSendTest()->format('d.m.Y H:i:s'),
				'startDate'  => is_null($this->test->getDateStartTest())  ? '' : $this->test->getDateStartTest()->format('d.m.Y H:i:s'),
				'finishDate' => is_null($this->test->getDateFinishTest()) ? '' : $this->test->getDateFinishTest()->format('d.m.Y H:i:s'),
				'totalTime'  => (is_null($this->test->getDateStartTest()) or is_null($this->test->getDateFinishTest()))
					? '' : abs($this->test->getDateFinishTest()->getTimestamp() - $this->test->getDateStartTest()->getTimestamp()),
				'answers'    => $this->_loadTestAnswers(),
			],
			'additionalParams' => $additionalParams,
		];
	}

	/**
	 * Загрузка ответов соискателя
	 * @return array
	 */
	private function _loadTestAnswers(): array {
		$answers = $this->entityManager->getRepository(ResaltAnswer::class)->findBy([
			'participant' => $this->test->getParticipantId()
		]);

		$arrAnswers = [];
		$prevQuestionDate = $this->test->getDateStartTest();
		foreach ($answers as $answerItem) {
			// Общее время ответа на вопрос
			$answerTotalTime = is_null($prevQuestionDate)
				? '' : abs($answerItem->getAnswerDate()->getTimestamp() - $prevQuestionDate->getTimestamp());

			$arrAnswers[] = [
				'questionId'=> $answerItem->getQuestion()->getId(),
				'question'  => $answerItem->getQuestion()->translate($this->locale)->getQuestionText(),
				'answerId'  => $answerItem->getId(),
				'answer'    => $answerItem->getAnswer() ?? '',
				'comment'   => $answerItem->getComment() ?? '',
				'score'     => $answerItem->getAnswerScore(),
				'dateTime'  => $answerItem->getAnswerDate()->format('d.m.Y H:i:s'),
				'totalTime' => $answerTotalTime,
			];

			$prevQuestionDate = $answerItem->getAnswerDate();
		}

		return $arrAnswers;
	}


	// ====== Реализация интерфейса: ITestResultsInterpreter
	// Часть методов интерфейса реализована в трейте TTestResultsInterpreter

	/**
	 * Получение параметров для Twig View (c отображением результатов)
	 * @param array $additionalParams
	 * @return array
	 */
	public function viewParams(array $additionalParams = []): array {
		return $this->_interpretation($additionalParams);
	}


	// ====== Реализация интерфейса: ITestResultsPdfInterpreter
	// Часть методов интерфейса реализована в трейте TTestResultsPdfInterpreter

	/**
	 * Получение параметров для Twig View (c отображением результатов в PDF-формате)
	 * @param array $additionalParams
	 * @return array
	 */
	public function pdfViewParams(array $additionalParams = []): array {
		return $this->_interpretation($additionalParams);
	}


	// ====== Реализация интерфейса: ITestResultsApiInterpreter

	/**
	 * Результат интерпретации для Api
	 * @param array $additionalParams
	 * @return array
	 */
	public function interpretateForApi(array $additionalParams = []): array {
		return $this->_interpretation($additionalParams);
	}
}