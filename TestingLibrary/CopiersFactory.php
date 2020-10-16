<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use Doctrine\ORM\EntityManagerInterface;
use wideweb\VKBundle\Entity\{Participant, Parts, User};

/**
 * Class CopiersFactory
 * Фабрика для создания обработчика ответа соискателя для конкретного теста
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
class CopiersFactory {
	/**
	 * @var EntityManagerInterface|null
	 */
	protected $entityManager = null;

	/**
	 * CopiersFactory constructor.
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	/**
	 * Копирование теста по коду копирования
	 * @param string $code
	 * @param Participant $participant
	 * @throws Exceptions\CopierNotDefinedException
	 * @throws Exceptions\IncorrectCopyCodeException
	 * @throws Exceptions\LimitUserBalanceException
	 * @throws Exceptions\TestNotFoundException
	 */
	public function copyTestByCode(string $code, Participant $participant): void {
		// Check user's balance
		$participantOwner = $participant->getCreator();
		if ($participantOwner->getAdditionalTest() - $participantOwner->getSend() <= 0) {
			throw new Exceptions\LimitUserBalanceException($participantOwner);
		}

		// Check copy code by mask
		if (! preg_match('/^\d+\-\w{4}$/', $code)) {
			throw new Exceptions\IncorrectCopyCodeException($code);
		}

		// Find source test by copy code
		$srcTest = $this->entityManager->getRepository(Parts::class)->findOneBy(['copyCode' => $code]);
		if (is_null($srcTest) or $srcTest->getStatusTest() !== Parts::PART_COMPLETE) {
			throw new Exceptions\TestNotFoundException('Source test not found or not completed');
		}

		// Find destination test
		$destTest = $this->entityManager->getRepository(Parts::class)->findOneBy([
			'participant_id' => $participant->getId(),
			'part' => $srcTest->getPart(),
		]);
		if (is_null($destTest)) {
			throw new Exceptions\TestNotFoundException('Destination test not found or not sended');
		}

		// copy all test's data
		$copier = $this->createCopier($destTest);
		$copier->copyParticipantData($srcTest->getParticipantObj(), $participant);
		$copier->copyTestData($srcTest, $destTest);
		$copier->copyTestAnswers($srcTest, $destTest);

		// У пользователя увеличиваем поле send на 1
		// Attention: после выполнения хранимой процедуры значение поля send в сущности
		// пользователя будет не синхронизированно с БД (в сущности останется старое значение поля)
		$this->entityManager->getRepository(User::class)->increaseSendedTests($participantOwner);
	}

	/**
	 * @param Parts $test
	 * @return ITestResultsCopier
	 * @throws Exceptions\CopierNotDefinedException
	 */
	protected function createCopier(Parts $test): ITestResultsCopier {
		$copierClass = __NAMESPACE__ . "\\Tests\\Test{$test->getPart()}\\TestCopier";
		if (! class_exists($copierClass)) {
			throw new Exceptions\CopierNotDefinedException($test);
		}

		return new $copierClass($this->entityManager);
	}
}