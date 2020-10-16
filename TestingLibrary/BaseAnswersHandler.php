<?php namespace wideweb\VKBundle\Libraries\TestingLibrary;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use wideweb\VKBundle\Entity\{Participant, Parts, User};
use Symfony\Component\Form\{FormFactory, FormInterface};

/**
 * Class BaseAnswersHandler
 * @package wideweb\VKBundle\Libraries\TestingLibrary
 */
abstract class BaseAnswersHandler {
	/**
	 * @var EntityManagerInterface|null
	 */
	protected $entityManager = null;

	/**
	 * @var string
	 */
	protected $locate = 'ru';

	/**
	 * @var Parts|null
	 */
	protected $test = null;

	/**
	 * Текущий шаг теста
	 * если null - тест завершен
	 * @var int|null
	 */
	protected $currentStep = null;

	/**
	 * Форма с вопросом (который на текущем шаге)
	 * @var FormInterface|null
	 */
	protected $questionForm = null;

	/**
	 * Флаг валидности отправленного ответа
	 * @var bool
	 */
	protected $isValidAnswer = false;

	/**
	 * BaseTest constructor.
	 * @param Parts $test
	 * @param EntityManagerInterface $entityManager
	 * @param string $locate
	 */
	public function __construct(Parts $test, EntityManagerInterface $entityManager, string $locate = 'ru') {
		$this->entityManager = $entityManager;
		$this->locate = $locate;
		$this->test   = $test;
		$this->_initCurrentStep();
	}

	/**
	 * Инициализация значения текущего шага
	 */
	private function _initCurrentStep() {
		$this->currentStep = (int) $this->test->getCurrentStep();
		if ($this->currentStep <= 0) {
			$this->currentStep = 1;
		}

		if ($this->isCompleteProcess()) {
			$this->currentStep = null;
		}
	}

	/**
	 * Возвращает данные в JSON-формате, которые будут
	 * передаваться в обработчики событий связанных с тестами
	 * (начало теста, завершение теста)
	 * @return string
	 */
	private function _getDataForEvent(): string {
		$participant = $this->test->getParticipantObj();
		return json_encode([
			'testId'        => $this->test->getId(),
			'testNum'       => $this->test->getPart(),
			'participantId' => $this->test->getParticipantId(),
			'vacancyId'     => ( is_null($participant->getVacancy()) ? 0 : $participant->getVacancy()->getId() ),
			'userId'        => $participant->getCreator()->getId(),
			'locale'        => $this->locate,
		]);
	}

	/**
	 * Получение типа формы вопроса
	 * @return string
	 */
	abstract protected function getQuestionFormType(): string;

	/**
	 * Получение объекта с данными для заполнения формы
	 * @return mixed
	 */
	abstract protected function getQuestionFormData();

	/**
	 * Получение настроек формы
	 * @return array
	 */
	abstract protected function getQuestionFormSettings(): array;

	/**
	 * Получение номера следующего вопроса
	 * если возвращается null - тест завершен
	 * @param $currentAnswer
	 * @return int|null
	 */
	abstract protected function getNextStep($currentAnswer): ?int;


	// ===== Реализация методов интерфейса ITestAnswersHandler

	/**
	 * Проверка: прохождение теста только началось
	 * @return bool
	 * @throws \Exception
	 */
	public function isBeginProcess(): bool {
		$isBegin = false;
		if (is_null($this->test->getDateStartTest())) {
			$this->test->setDateStartTest(new \DateTime('now'));
			$this->entityManager->persist($this->test);
			$this->entityManager->flush();
			$isBegin = true;
		}

		return $isBegin;
	}

	/**
	 * Проверка: прохождение теста завершено
	 * @return bool
	 */
	public function isCompleteProcess(): bool {
		return (
			$this->test->getStatusTest() === Parts::PART_COMPLETE and
			! is_null($this->test->getDateFinishTest())
		);
	}

	/**
	 * Проверка теста на возможность прохождения
	 * @param string $checkedHash
	 * @throws Exceptions\ParticipantAccessDeniedException
	 * @throws Exceptions\TestBlockedException
	 * @throws Exceptions\TestCompletedException
	 */
	public function checkTestForProcessing(string $checkedHash = ''): void {
		$participant = $this->test->getParticipantObj();
		if (strtolower($participant->getIdentifier()) !== strtolower($checkedHash)) {
			throw new Exceptions\ParticipantAccessDeniedException();
		}

		if (
			$participant->getStatus() === Participant::STATUS_DENIED or     // Отказано
			$participant->getStatus() === Participant::STATUS_REMOVED       // Удалено
		) {
			throw new Exceptions\TestBlockedException($participant, Exceptions\TestBlockedException::TEST_WAS_BLOCKED);
		}

		if (
			$participant->getStatus() === Participant::STATUS_REFUSED or    // Отказался
			$participant->getStatus() === Participant::STATUS_EXPIRED       // Отказался
		) {
			throw new Exceptions\TestBlockedException($participant, Exceptions\TestBlockedException::PARTICIPANT_REFUSE);
		}

		if (! is_null($participant->getVacancy()) and $participant->getVacancy()->getArchive()) {
			throw new Exceptions\TestBlockedException($participant, Exceptions\TestBlockedException::TEST_WAS_BLOCKED);
		}

		if ($this->isCompleteProcess()) {
			throw new Exceptions\TestCompletedException($this->test);
		}
	}

	/**
	 * Создание формы с вопросом
	 * @param FormFactory $formFactory
	 * @throws Exceptions\TestCompletedException
	 */
	public function createQuestionForm(FormFactory $formFactory): void {
		if ($this->isCompleteProcess()) {
			throw new Exceptions\TestCompletedException($this->test);
		}

		$this->questionForm = $formFactory->create(
			$this->getQuestionFormType(),
			$this->getQuestionFormData(),
			$this->getQuestionFormSettings()
		);
	}

	/**
	 * Проверка отправки ответа с формы вопроса
	 * (Соискатель ответил на вопрос из формы)
	 * @param Request $request
	 * @return bool
	 * @throws \Exception
	 */
	public function isSubmittedAnswer(Request $request): bool {
		if (is_null($this->questionForm)) {
			throw new \Exception('Question form is not defined');
		}

		$this->questionForm->handleRequest($request);
		$this->isValidAnswer = $this->questionForm->isSubmitted() and $this->questionForm->isValid();
		return $this->isValidAnswer;
	}

	/**
	 * Обработка ответа соискателя из формы вопроса
	 * @throws \Exception
	 */
	public function handleSubmittedAnswer(): void {
		if ($this->isValidAnswer) {
			$currentAnswer = $this->questionForm->getData();
			// Получение номера следующего вопроса
			// $nextStep = null - тест завершен
			$nextStep = $this->getNextStep($currentAnswer);
			if (is_null($nextStep)) {
				$this->test->setDateFinishTest(new \DateTime('now'));
				$this->test->setStatusTest(Parts::PART_COMPLETE);
				$this->test->setCopyCode(Parts::generateCopyCode($this->test->getPart()));
				// todo-KorolOFF: Удалить этот костыль после рефакторинга всех тестов
				$this->entityManager->persist(
					$this->test->getParticipantObj()->setStatus(Participant::STATUS_COMPLETED)
				);
			} else {
				$this->test->setCurrentStep($nextStep);
				$this->test->setStatusTest(Parts::PART_PROCESSING);
			}

			$this->entityManager->persist($currentAnswer);
			$this->entityManager->persist($this->test);
			$this->entityManager->flush();

			// при завершении теста
			// списываем тест с баланса
			if (is_null($nextStep)) {
				$ownerUser = $this->test->getParticipantObj()->getCreator();
				$this->entityManager->getRepository(User::class)->increaseSendedTests($ownerUser);
			}
		}
	}

	/**
	 * Получение списка параметров для отрисовки Twig View,
	 * в котором отображается форма прохождения теста
	 * @return array
	 */
	public function getProcessingViewParams(): array {
		return [
			'testData'     => $this->test,
			'participant'  => $this->test->getParticipantObj(),
			'questionForm' => $this->questionForm->createView(),
		];
	}

	/**
	 * Возвращает данные в JSON-формате, которые будут
	 * передаваться в обработчики события начала тестирования
	 * @return string
	 */
	public function getStartTestEventDataInJSON(): string {
		return $this->_getDataForEvent();
	}

	/**
	 * Возвращает данные в JSON-формате, которые будут
	 * передаваться в обработчики события завершения тестирования
	 * @return string
	 */
	public function getCompleteTestEventDataInJSON(): string {
		return $this->_getDataForEvent();
	}

	/**
	 * Путь до Twig View (в которой отображается форма прохождения теста)
	 * @return string
	 */
	public function getProcessingViewPath(): string {
		return "widewebVKBundle:TestsProcessing:test{$this->test->getPart()}.html.twig";
	}
}