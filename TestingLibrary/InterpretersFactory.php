<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use wideweb\VKBundle\Entity\{User, Parts};

/**
 * Class InterpretersFactory
 * Фабрика для создания интерпретатора результатов теста
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
class InterpretersFactory {

	// возможные форматы интерпретации
	public const FORMAT_HTML     = 'html';      // обычный формат интерпретации
	public const FORMAT_PDF      = 'pdf';       // интерпретация в формате PDF
	public const FORMAT_PDF_SEND = 'pdf_send';  // интерпретация в формате PDF + отправка на email

	/**
	 * @var EntityManagerInterface|null
	 */
	protected $entityManager = null;

	/**
	 * InterpretersFactory constructor.
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	/**
	 * Определение запрашиваемого формата интерпретации
	 * @param Request $request
	 * @return string
	 */
	public function detectInterpretationFormat(Request $request): string {
		$format = strtolower((string) $request->get('format', ''));
		if ($format === self::FORMAT_PDF) {
			return self::FORMAT_PDF;
		} else if ($format === self::FORMAT_PDF_SEND) {
			return self::FORMAT_PDF_SEND;
		}

		return self::FORMAT_HTML;
	}

	/**
	 * @param int $testId id теста, для которого создаем интерпретатор результатов
	 * @param User|null $currentUser если передан null, то проверка не производится
	 * @param string $locale локаль
	 * @return ITestResultsInterpreter
	 * @throws Exceptions\AccessDeniedException
	 * @throws Exceptions\InterpretatorNotDefinedException
	 * @throws Exceptions\TestNotFoundException
	 */
	public function createSimpleInterpretator(int $testId, ?User $currentUser, string $locale = 'ru'): ITestResultsInterpreter {
		$test = $this->entityManager->find(Parts::class, $testId);
		if (is_null($test)) {
			throw new Exceptions\TestNotFoundException();
		}

		return $this->createInterpretator($test, $currentUser, $locale);
	}

	/**
	 * @param int $participantId - id соискателя
	 * @param int $testNum - номер теста
	 * @param User|null $currentUser - если передан null, то проверка владельца не производится
	 * @param string $locale - локаль
	 * @return ITestResultsApiInterpreter
	 * @throws Exceptions\AccessDeniedException
	 * @throws Exceptions\InterpretatorNotDefinedException
	 * @throws Exceptions\TestNotFoundException
	 */
	public function createApiInterpretator(int $participantId, int $testNum, ?User $currentUser, string $locale = 'ru'): ITestResultsApiInterpreter {
		$test = $this->entityManager->getRepository(Parts::class)->findOneBy([
			'participant_id' => $participantId,
			'part' => $testNum,
		]);
		if (is_null($test)) {
			throw new Exceptions\TestNotFoundException();
		}

		$interpretator = $this->createInterpretator($test, $currentUser, $locale);
		if (! ($interpretator instanceof ITestResultsApiInterpreter)) {
			throw new Exceptions\InterpretatorNotDefinedException($test);
		}

		return $interpretator;
	}

	/**
	 * @param Parts $test тест, для которого создаем интерпретатор результатов
	 * @param User|null $currentUser если передан null, то проверка владельца не производится
	 * @param string $locale локаль
	 * @return ITestResultsInterpreter
	 * @throws Exceptions\AccessDeniedException
	 * @throws Exceptions\InterpretatorNotDefinedException
	 */
	protected function createInterpretator(Parts $test, ?User $currentUser, string $locale = 'ru'): ITestResultsInterpreter {
		if (! is_null($currentUser)) {
			$ownerUser = $test->getParticipantObj()->getCreator();
			if ($ownerUser->getId() !== $currentUser->getId()) {
				if (! $this->entityManager->getRepository(User::class)->isParent($ownerUser, $currentUser)) {
					throw new Exceptions\AccessDeniedException();
				}
			}
		}

		$interpreterClass = __NAMESPACE__ . "\\Tests\\Test{$test->getPart()}\\ResultsInterpretator";
		if (! class_exists($interpreterClass)) {
			throw new Exceptions\InterpretatorNotDefinedException($test);
		}

		return new $interpreterClass($test, $this->entityManager, $locale);
	}
}