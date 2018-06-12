<?php
// api/src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Service\CrawlerService;

class CrawlerCommand extends Command
{
    private $crawlerService;

    public function __construct(CrawlerService $crawlerService)
    {
        $this->crawlerService = $crawlerService;

        parent::__construct();
    }
    
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:crawl')
            ->setDescription('Crawl to create tags')
            ->addArgument('brandId', InputArgument::REQUIRED, 'Brand to crawl ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->crawlerService->setOutput($output);
        $this->crawlerService->crawl();
    }
}

