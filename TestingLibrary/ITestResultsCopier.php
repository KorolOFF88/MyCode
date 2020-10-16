<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use wideweb\VKBundle\Entity\{Participant, Parts};

/**
 * Interface ITestResultsCopier
 * Интерфейс копировальщика результатов теста
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
interface ITestResultsCopier {
	/**
	 * Копирование данных соискателя
	 * @param Participant $srcParticipant
	 * @param Participant $destParticipant
	 */
	public function copyParticipantData(Participant $srcParticipant, Participant $destParticipant): void;

	/**
	 * Копирование данных теста
	 * @param Parts $srcTest
	 * @param Parts $destTest
	 */
	public function copyTestData(Parts $srcTest, Parts $destTest): void;

	/**
	 * Копирование ответов соискателя
	 * @param Parts $srcTest
	 * @param Parts $destTest
	 */
	public function copyTestAnswers(Parts $srcTest, Parts $destTest): void;
}