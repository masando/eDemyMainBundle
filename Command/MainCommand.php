<?php
namespace eDemy\MainBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\Process;

class MainCommand extends Command
{
    private $input, $output;
    
    protected function configure()
    {
        $this
            ->setName('edemy:main')
            ->setDescription('main management program')
            ->addArgument('host', null, InputArgument::REQUIRED, 'user@host')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->host = $input->getArgument('host');
        $helper = $this->getHelper('question');

        //Confirmation
        $confirm = new ConfirmationQuestion('Action?', false);        
        //$answer = $helper->ask($input, $output, $question);
        //$this->output->writeln($answer);

        //Question
        $question = new Question('Question?', 'default');
        //$answer = $helper->ask($input, $output, $question);
        //$this->output->writeln($answer);

        //Choice
        $question = new ChoiceQuestion(
            'Select color (defaults to red)',
            array('red', 'blue', 'yellow'),
            0
        );
        $question->setErrorMessage('Color %s is invalid.');
        //$color = $helper->ask($input, $output, $question);
        //$output->writeln('You have just selected: '.$color);

        //Multiple
        $question = new ChoiceQuestion(
            'Please select your favorite colors (defaults to red and blue)',
            array('red', 'blue', 'yellow'),
            '0,1'
        );
        $question->setMultiselect(true);
        //$colors = $helper->ask($input, $output, $question);
        //$output->writeln('You have just selected: ' . implode(', ', $colors));

        //Autocompletion
        $bundles = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
        $question = new Question('Please enter the name of a bundle', 'FooBundle');
        $question->setAutocompleterValues($bundles);
        $name = $helper->ask($input, $output, $question);
        $output->writeln($name);

        //Hidden
        $question = new Question('What is the database password?');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);
        $output->writeln($password);

        return;
    }
}

