<?php
namespace Redgecko\Magnalister\Console;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Afterupdate extends Command
{
    private $objectManager;

    /**
     * @method __construct
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('magnalister:afterupdate');
        $this->setDescription('Update configuration of magnalister database tables');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $output->writeln('<info>' . 'Working on it ...' . '</info>');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');

            //$orderState = $connection->getConnection(ResourceConnection::DEFAULT_CONNECTION)->fetchRow('SELECT state FROM `'.$tblSalesOrder.'` WHERE status="'.$orderStatus.'"');

            $connection->getConnection()->delete('magnalister_config', 'mkey =' . "'after-update'");
            $output->writeln('<info>' . 'please open magnalister page in Shop Admin to finish updating process.' . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
