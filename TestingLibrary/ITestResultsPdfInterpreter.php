<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

/**
 * Interface ITestResultsPdfInterpreter
 * Интерфейс интерпретатора результатов теста в PDF-формате
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
interface ITestResultsPdfInterpreter {
	/**
	 * Генерирование имени PDF-файла
	 * @param callable $translitFunc ф-ция для транслитерации
	 * @return string
	 */
	public function pdfGenerateFileName(callable $translitFunc): string;

	/**
	 * Путь до Twig View (отображение результатов в PDF-формате)
	 * @return string
	 */
	public function pdfViewPath(): string;

	/**
	 * Получение параметров для Twig View (c отображением результатов в PDF-формате)
	 * @param array $additionalParams
	 * @return array
	 */
	public function pdfViewParams(array $additionalParams = []): array;

	/**
	 * Параметры для knp_snappy.pdf (для формирования PDF)
	 * @return array
	 */
	public function pdfBuildParams(): array;

	/**
	 * Генерация темы email-сообщения
	 * @param string $strWithPlaceholders
	 * @return string
	 */
	public function generateEmailSubject(string $strWithPlaceholders): string;

	/**
	 * Генерация тела email-сообщения
	 * @param string $strWithPlaceholders
	 * @return string
	 */
	public function generateEmailMessage(string $strWithPlaceholders): string;

	/**
	 * Генерация наименования компании
	 * @param string $defaultValue
	 * @return string
	 */
	public function generateEmailCompany(string $defaultValue): string;
}