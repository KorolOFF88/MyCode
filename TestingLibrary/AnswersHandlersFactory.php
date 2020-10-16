<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use Doctrine\ORM\EntityManagerInterface;
use wideweb\VKBundle\Entity\Parts;

/**
 * Class AnswersHandlersFactory
 * Фабрика для создания обработчика ответа соискателя для конкретного теста
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
class AnswersHandlersFactory {

	/**
	 * @var EntityManagerInterface|null
	 */
	protected $entityManager = null;

	/**
	 * AnswersHandlersFactory constructor.
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	/**
	 * @param int $testId
	 * @param string $locale
	 * @return ITestAnswersHandler
	 * @throws Exceptions\HandlerNotDefinedException
	 * @throws Exceptions\TestNotFoundException
	 */
	public function createHandler(int $testId, string $locale = 'ru'): ITestAnswersHandler {
		$test = $this->entityManager->find(Parts::class, $testId);
		if (is_null($test)) {
			throw new Exceptions\TestNotFoundException();
		}

		$handlerClass = __NAMESPACE__ . "\\Tests\\Test{$test->getPart()}\\AnswersHandler";
		if (! class_exists($handlerClass)) {
			throw new Exceptions\HandlerNotDefinedException($test);
		}

		return new $handlerClass($test, $this->entityManager, $locale);
	}
}