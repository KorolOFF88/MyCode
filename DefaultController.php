<?php

namespace wideweb\VKBundle\Controller;

use Symfony\Component\HttpFoundation\{Request, Response};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\{Route, Template};
use wideweb\VKBundle\Entity\{Participant, ResaltAnswer, ResaltQuestion, User, Parts};
use wideweb\VKBundle\Libraries\TestingLibrary\{ITestResultsPdfInterpreter, Exceptions};


class DefaultController extends BaseController
{

	/**
	 * @Route(
	 *     "/testing-process/{testId}/{participantHash}",
	 *     name="testing_process",
	 *     requirements={"testId"="\d+", "participantHash"="[a-zA-Z0-9]{64}"}
	 * )
	 * @param Request $request
	 * @param int $testId
	 * @param string $participantHash
	 * @return Response
	 */
	public function testingProcessAction(Request $request, int $testId, string $participantHash): Response {

		try {

			// создаем обработчик ответов для теста
			$testHandler = $this->get('test.answers_handlers.factory')->createHandler($testId, $request->getLocale());
			$testHandler->checkTestForProcessing($participantHash);
			if ($testHandler->isBeginProcess()) {
				$this->get('old_sound_rabbit_mq.test_started_producer')->publish(
					$testHandler->getStartTestEventDataInJSON(), ''
				);
			}

			$testHandler->createQuestionForm($this->get('form.factory'));
			if ($testHandler->isSubmittedAnswer($request)) {
				$testHandler->handleSubmittedAnswer();
				if ($testHandler->isCompleteProcess()) {
					$this->get('old_sound_rabbit_mq.test_completed_producer')->publish(
						$testHandler->getCompleteTestEventDataInJSON(), ''
					);
				}

				return $this->redirectToRoute('testing_process', [
					'testId' => $testId, 'participantHash' => $participantHash
				]);
			}

			return $this->render($testHandler->getProcessingViewPath(), $testHandler->getProcessingViewParams());

		}
		catch (Exceptions\TestCompletedException $exception) {
			return $this->render('widewebVKBundle:TestsProcessing:testComplete.html.twig', [
				'isCountryValid' => $this->renderOpenApiVK($this->getIpCountry(), $request),
				'testMetaData'   => $exception->getTest(),
				'nextTest'       => $exception->getNextWaitingTest(),
			]);
		}
		catch (Exceptions\TestBlockedException $exception) {
			return $this->render('widewebVKBundle:TestsProcessing:testErrorPage.html.twig', [
				'exception' => $exception,
			]);
		}
		catch (Exceptions\ParticipantAccessDeniedException | Exceptions\TestNotFoundException $exception) {
			throw new NotFoundHttpException($exception->getMessage(), $exception);
		}
		catch (\Exception $exception) {
			$message  = "\nTest's processing fatal error\n";
			$message .= __METHOD__ . PHP_EOL;
			$message .= "Params:\n=====\n";
			$message .= "testId={$testId}\nparticipantHash={$participantHash}\n";
			$message .= "Exception message: {$exception->getMessage()}\n";
			$this->get('tgapi')->sendNotify($message, 500);
			return new Response('Fatal error.', Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @Route(
	 *     "/test-results/{testId}",
	 *     name="test_results",
	 *     requirements={"testId"="\d+"}
	 * )
	 * @param Request $request
	 * @param int $testId
	 * @return Response
	 */
	public function getTestResultsAction(Request $request, int $testId): Response {
		$currUser = $this->get('security.token_storage')->getToken()->getUser();
		if (! is_object($currUser)) {
			return $this->redirectToRoute('index');
		}

		try {

			$interpretersFactory = $this->get('test.interpreters.factory');
			$interpreter = $interpretersFactory->createSimpleInterpretator($testId, $currUser, $request->getLocale());

			// определяем запрашиваемый формат интерпретации
			$requestedFormat = $interpretersFactory->detectInterpretationFormat($request);
			if (
				($requestedFormat === $interpretersFactory::FORMAT_PDF or $requestedFormat === $interpretersFactory::FORMAT_PDF_SEND) and
				$interpreter instanceof ITestResultsPdfInterpreter
			) {
				$receiverAddress = strtolower((string) $request->get('email', ''));
				if ($requestedFormat === $interpretersFactory::FORMAT_PDF_SEND and ! filter_var($receiverAddress, FILTER_VALIDATE_EMAIL)) {
					return new Response('invalid_email');
				}

				// Генерация результатов в PDF-формате
				$pdfFileName = $interpreter->pdfGenerateFileName([Tool::class, 'translit']);
				$pdfContent  = $this->get('knp_snappy.pdf')->getOutputFromHtml(
					$this->renderView($interpreter->pdfViewPath(), $interpreter->pdfViewParams()),
					$interpreter->pdfBuildParams()
				);

				if ($requestedFormat === $interpretersFactory::FORMAT_PDF) {
					return new PdfResponse($pdfContent, $pdfFileName);
				}

				// Генерация данных и отправка сообщения
				$subject = $interpreter->generateEmailSubject($this->i18n('index__generatePdf', 'EMAIL_SUBJECT'));
				$message = $interpreter->generateEmailMessage($this->i18n('index__generatePdf', 'EMAIL_MESSAGE'));
				$company = $interpreter->generateEmailCompany($this->i18n('index__generatePdf', 'EMAIL_COMPANY_DEFAULT'));

				$mail = new \Swift_Message($subject, $message);
				$mail->setTo($receiverAddress);
				$mail->setFrom(['test@test.ru' => $company]);
				$mail->attach(new \Swift_Attachment($pdfContent, $pdfFileName, 'application/pdf'));
				$this->get('mailer.wrapper')->send($mail, OurMailer::TRANSPORT_SEND_PULSE);
				return new Response('ok');
			}

			return $this->render($interpreter->viewPath(), $interpreter->viewParams([ 'currentUserId' => $currUser->getId() ]));

		} catch (\Swift_TransportException $exception) {
			return new Response('send_error');
		} catch (
			Exceptions\TestNotFoundException |
			Exceptions\AccessDeniedException |
			Exceptions\TestNotCompletedException |
			Exceptions\InterpretatorNotDefinedException $exception
		) {
			throw new NotFoundHttpException($exception->getMessage(), $exception);
		} catch (\Exception $exception) {
			$message  = "\nTest's results interpretating fatal error\n";
			$message .= __METHOD__ . PHP_EOL;
			$message .= "Params:\n=====\n";
			$message .= "testId={$testId}\n";
			$message .= "Exception message: {$exception->getMessage()}\n";
			$this->get('tgapi')->sendNotify($message, 500);
			return new Response('Fatal error', Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}
