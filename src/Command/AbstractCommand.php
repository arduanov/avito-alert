<?php
namespace App\Command;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    protected $app;
    protected $output;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->output = $output;
    }

    protected function comment($message)
    {
        $this->output->writeln('<comment>' . $message . '</comment>');

    }

    protected function info($message)
    {
        $this->output->writeln('<info>' . $message . '</info>');

    }

    protected function error($message)
    {
        $this->output->writeln('<error>' . $message . '</error>');

    }

    protected function question($message)
    {
        $this->output->writeln('<question>' . $message . '</question>');

    }

}
