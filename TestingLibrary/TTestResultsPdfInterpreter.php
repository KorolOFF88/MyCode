<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

/**
 * Trait TTestResultsPdfInterpreter
 * Некоторые общие методы для интерпретаторов умеющих генерировать PDF
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
trait TTestResultsPdfInterpreter {
	/**
	 * Генерирование имени PDF-файла
	 * @param callable $translitFunc ф-ция для транслитерации
	 * @return string
	 */
	public function pdfGenerateFileName(callable $translitFunc): string {
		$participant = $this->test->getParticipantObj();
		$fileName = $this->pdfFileNamePrefix ?? "Test{$this->test->getPart()}";
		if ($this->locale === 'ru' or $this->locale === 'en') {
			$fileName .= $translitFunc($participant->getName() . '_' . $participant->getLastName()) . '_' . date('dmY') . '.pdf';
		} else {
			$fileName .= "participant{$participant->getId()}.pdf";
		}

		return $fileName;
	}

	/**
	 * Путь до Twig View (отображение результатов в PDF-формате)
	 * @return string
	 */
	public function pdfViewPath(): string {
		return "widewebVKBundle:TestsInterpretatingPdf:test{$this->test->getPart()}.html.twig";
	}

	/**
	 * Параметры для knp_snappy.pdf (для формирования PDF)
	 * @return array
	 */
	public function pdfBuildParams(): array {
		return [ /* Параметры для генерации PDF */ ];
	}

	/**
	 * Генерация темы email-сообщения
	 * @param string $strWithPlaceholders
	 * @return string
	 */
	public function generateEmailSubject(string $strWithPlaceholders): string {
		return str_replace('%{ParticipantId}', $this->test->getParticipantObj()->getId(), $strWithPlaceholders);
	}

	/**
	 * Генерация тела email-сообщения
	 * @param string $strWithPlaceholders
	 * @return string
	 */
	public function generateEmailMessage(string $strWithPlaceholders): string {
		$participant = $this->test->getParticipantObj();
		$placeholders = [ '%{Name}' => $participant->getName(), '%{LastName}' => $participant->getLastName() ];
		return str_replace(array_keys($placeholders), array_values($placeholders), $strWithPlaceholders);
	}

	/**
	 * Генерация наименования компании
	 * @param string $defaultValue
	 * @return string
	 */
	public function generateEmailCompany(string $defaultValue): string {
		$company = $this->test->getParticipantObj()->getCreator()->getCompany();
		return empty($company) ? $defaultValue : $company;
	}
}