<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use Doctrine\ORM\EntityManagerInterface;
use wideweb\VKBundle\Entity\{Participant, Parts};

/**
 * Class BaseTestResultsCopier
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
abstract class BaseTestResultsCopier {
	/**
	 * @var EntityManagerInterface|null
	 */
	protected $entityManager = null;

	/**
	 * BaseTestResultsCopier constructor.
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	/**
	 * Копирование данных соискателя
	 * @param Participant $srcParticipant
	 * @param Participant $destParticipant
	 */
	public function copyParticipantData(Participant $srcParticipant, Participant $destParticipant): void {
		if (empty($destParticipant->getName()) and ! empty($srcParticipant->getName())) {
			$destParticipant->setName($srcParticipant->getName());
		}

		if (empty($destParticipant->getLastName()) and ! empty($srcParticipant->getLastName())) {
			$destParticipant->setLastName($srcParticipant->getLastName());
		}

		if (empty($destParticipant->getAge()) and ! empty($srcParticipant->getAge())) {
			$destParticipant->setAge($srcParticipant->getAge());
		}

		if (empty($destParticipant->getTel()) and ! empty($srcParticipant->getTel())) {
			$destParticipant->setTel($srcParticipant->getTel());
		}

		if (empty($destParticipant->getCity()) and ! empty($srcParticipant->getCity())) {
			$destParticipant->setCity($srcParticipant->getCity());
		}

		if (empty($destParticipant->getSex()) and ! empty($srcParticipant->getSex())) {
			$destParticipant->setSex($srcParticipant->getSex());
		}

		$destParticipant->setStatus(Participant::STATUS_COMPLETED);
		$this->entityManager->persist($destParticipant);
		$this->entityManager->flush();
	}

	/**
	 * Копирование данных теста
	 * @param Parts $srcTest
	 * @param Parts $destTest
	 * @throws \Exception
	 */
	public function copyTestData(Parts $srcTest, Parts $destTest): void {
		$currDate = new \DateTime('now');
		$destTest->setStatusTest(Parts::PART_COMPLETE);
		$destTest->setCurrentStep($srcTest->getCurrentStep());
		$destTest->setIsRead(false);
		$destTest->setRandomOrder($srcTest->getRandomOrder());
		$destTest->setDateStartTest($srcTest->getDateStartTest() ?? $currDate);
		$destTest->setDateFinishTest($srcTest->getDateFinishTest() ?? $currDate);
		$destTest->setCopyFromTestId($srcTest->getId());
		$destTest->setCopyDate($currDate);
		$this->entityManager->persist($destTest);
		$this->entityManager->flush();
	}
}