<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use wideweb\VKBundle\Entity\Participant;

/**
 * Interface ITestResultsInterpreter
 * Интерфейс интерпретатора результатов теста
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
interface ITestResultsInterpreter {
	/**
	 * Путь до Twig View (отображение результатов)
	 * @return string
	 */
	public function viewPath(): string;

	/**
	 * Получение параметров для Twig View (c отображением результатов)
	 * @param array $additionalParams
	 * @return array
	 */
	public function viewParams(array $additionalParams = []): array;

	/**
	 * @deprecated
	 * Удалить этот метод
	 * Нужен только для встраиваемого шаблона
	 * "widewebVKBundle:TestsInterpretating:_statusPanel.html.twig"
	 * @return Participant
	 */
	public function getParticipant(): Participant;
}