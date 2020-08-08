<?php

namespace App\Command;

use App\Closure\Closure;
use App\Entity\Message;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddMessageCommand extends Command
{
    protected static $defaultName = 'app:add-message';

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a new message to the database.')
            ->addArgument('body', InputArgument::OPTIONAL, 'Full body message.')
            ->addArgument('active', InputArgument::OPTIONAL, 'Set message active.');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (!$input->getArgument('body')) {
            $argument = $this->getDefinition()->getArgument('body');
            $question = new Question($argument->getDescription() . "\n");
            $question->setNormalizer(Closure::trimValue());
            $question->setValidator(Closure::notBlank());
            $answer = $helper->ask($input, $output, $question);
            $input->setArgument('body', $answer);
        }

        if (!$input->getArgument('active')) {
            $question = new ChoiceQuestion($argument->getDescription() . "\n", ['no', 'yes']);
            $question->setErrorMessage('Value %s is invalid.');
            $question->setNormalizer(Closure::checkChoices());
            $answer = $helper->ask($input, $output, $question);
            $input->setArgument('active', $answer);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action (y/n)? ', false);

        $io = new SymfonyStyle($input, $output);
        if ($helper->ask($input, $output, $question)) {
            $message = new Message();
            $message->setBody($input->getArgument('body'));
            $message->setActive($input->getArgument('active'));
            $em = $this->registry->getManager();
            $em->persist($message);
            $em->flush();
            $io->success('Message successfully saved with ID: '.$message->getId());
            return 0;
        }
        $io->error('Message not saved.');
        return 1;
    }
}
