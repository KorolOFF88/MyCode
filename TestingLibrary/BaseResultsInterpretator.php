<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use Doctrine\ORM\EntityManagerInterface;
use wideweb\VKBundle\Entity\Parts;

/**
 * Class BaseResultsInterpretator
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
abstract class BaseResultsInterpretator {

	/**
	 * @var Parts|null
	 */
	protected $test = null;

	/**
	 * @var EntityManagerInterface|null
	 */
	protected $entityManager = null;

	/**
	 * @var string
	 */
	protected $locale = 'ru';

	/**
	 * BaseResultsInterpretator constructor.
	 * @param Parts $test
	 * @param EntityManagerInterface $entityManager
	 * @param string $locale
	 * @throws Exceptions\TestNotCompletedException
	 */
	public function __construct(Parts $test, EntityManagerInterface $entityManager, string $locale = 'ru') {
		$this->checkInterpretatingOpportunity($test);
		$this->entityManager = $entityManager;
		$this->locale = $locale;
		$this->test = $test;
	}

	/**
	 * Проверка теста на возможность интерпретации
	 * @param Parts $test
	 * @throws Exceptions\TestNotCompletedException
	 */
	protected function checkInterpretatingOpportunity(Parts $test): void {
		if ($test->getStatusTest() !== Parts::PART_COMPLETE) {
			throw new Exceptions\TestNotCompletedException($test);
		}
	}

	/**
	 * Проверка: тест просматривается впервые
	 * @return bool
	 */
	protected function isFirstViewing(): bool {
		$isFirstViewing = false;
		if (empty($this->test->getIsRead())) {
			$this->test->setIsRead(true);
			$this->entityManager->persist($this->test);
			$this->entityManager->flush();
			$isFirstViewing = true;
		}

		return $isFirstViewing;
	}
}