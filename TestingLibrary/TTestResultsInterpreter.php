<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use wideweb\VKBundle\Entity\Participant;

/**
 * Trait TTestResultsInterpreter
 * Некоторые общие методы для интерпретатора
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
trait TTestResultsInterpreter {
	/**
	 * Путь до Twig View (отображение результатов)
	 * @return string
	 */
	public function viewPath(): string {
		return "widewebVKBundle:TestsInterpretating:test{$this->test->getPart()}.html.twig";
	}

	/**
	 * @deprecated
	 * Удалить этот метод
	 * Нужен только для встраиваемого шаблона
	 * "widewebVKBundle:TestsInterpretating:_statusPanel.html.twig"
	 * @return Participant
	 */
	public function getParticipant(): Participant {
		return $this->test->getParticipantObj();
	}
}