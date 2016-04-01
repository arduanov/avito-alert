<?php

namespace App\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('worker:command')
            ->setDescription('Worker')
            ->setDefinition([
                new InputArgument('service', InputArgument::REQUIRED),
                new InputArgument('method', InputArgument::REQUIRED),
                new InputArgument('data', InputArgument::OPTIONAL, '', '[]'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $input->getArgument('service');
        $method = $input->getArgument('method');
        $data = $input->getArgument('data');
        $data = json_decode($data, true);

        call_user_func_array([$this->app[$service], $method], $data);
    }
}