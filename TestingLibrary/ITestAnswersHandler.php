<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ITestAnswersHandler
 * Интерфейс предназначен для обработки ответов соискателей на вопросы теста
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
interface ITestAnswersHandler {
	/**
	 * Проверка теста на возможность прохождения
	 * @param string $checkedHash
	 */
	public function checkTestForProcessing(string $checkedHash = ''): void;

	/**
	 * Проверка: прохождение теста только началось
	 * @return bool
	 */
	public function isBeginProcess(): bool;

	/**
	 * Проверка: прохождение теста завершено
	 * @return bool
	 */
	public function isCompleteProcess(): bool;

	/**
	 * Создание формы с вопросом на текущем шаге
	 * @param FormFactory $formFactory
	 */
	public function createQuestionForm(FormFactory $formFactory): void;

	/**
	 * Проверка отправки ответа с формы вопроса
	 * (Соискатель ответил на вопрос из формы)
	 * @param Request $request
	 * @return bool
	 */
	public function isSubmittedAnswer(Request $request): bool;

	/**
	 * Обработка ответа соискателя из созданной формы вопроса
	 */
	public function handleSubmittedAnswer(): void;

	/**
	 * Путь до Twig View (в которой отображается форма прохождения теста)
	 * @return string
	 */
	public function getProcessingViewPath(): string;

	/**
	 * Получение списка параметров для отрисовки Twig View,
	 * в котором отображается форма прохождения теста
	 * @return array
	 */
	public function getProcessingViewParams(): array;

	/**
	 * Возвращает данные в JSON-формате, которые будут
	 * передаваться в обработчики события начала тестирования
	 * @return string
	 */
	public function getStartTestEventDataInJSON(): string;

	/**
	 * Возвращает данные в JSON-формате, которые будут
	 * передаваться в обработчики события завершения тестирования
	 * @return string
	 */
	public function getCompleteTestEventDataInJSON(): string;
}