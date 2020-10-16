<?php namespace wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1;

use wideweb\VKBundle\Entity\{Parts, ResaltAnswer};
use wideweb\VKBundle\Libraries\TestingLibrary\{
	BaseTestResultsCopier, ITestResultsCopier
};

/**
 * Class TestCopier
 * Копировальщик результатов теста Резалт
 * @package wideweb\VKBundle\Libraries\TestingLibrary\Tests\Test1
 */
class TestCopier extends BaseTestResultsCopier implements ITestResultsCopier {
	/**
	 * Копирование ответов соискателя
	 * @param Parts $srcTest
	 * @param Parts $destTest
	 * @throws \Exception
	 */
	public function copyTestAnswers(Parts $srcTest, Parts $destTest): void {
		$answers = $this->entityManager->getRepository(ResaltAnswer::class)
									   ->findBy(['participant' => $srcTest->getParticipantObj()]);
		if (! empty($answers)) {
			foreach ($answers as $answerItem) {
				$tmpAnswer = new ResaltAnswer($destTest->getParticipantObj(), $answerItem->getQuestion());
				$tmpAnswer->setAnswerDate($answerItem->getAnswerDate());
				$tmpAnswer->setAnswer($answerItem->getAnswer());
				$tmpAnswer->setComment($answerItem->getComment());
				$this->entityManager->persist($tmpAnswer);
			}
			$this->entityManager->flush();
		}
	}
}