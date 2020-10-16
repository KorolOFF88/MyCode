<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

/**
 * Interface ITestResultsApiInterpreter
 * Интерфейс интерпретатора результатов теста (для использования в API)
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
interface ITestResultsApiInterpreter {
	/**
	 * Результат интерпретации для Api
	 * @param array $additionalParams
	 * @return array
	 */
	public function interpretateForApi(array $additionalParams = []): array;
}