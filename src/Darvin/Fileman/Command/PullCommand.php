<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Pull command
 */
class PullCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        if (null === $this->getName()) {
            $this->setName('pull');
        }

        $this->setDescription('Pulls remote files to local directory');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $localManager  = $this->createLocalManager($input);
        $remoteManager = $this->createRemoteManager($input, $output);

        $io->comment('Archiving remote files...');
        $remoteManager->archiveFiles();

        $io->comment('Downloading archives...');
        $remoteManager->downloadArchives($localManager->getProjectPath());

        $io->comment('Removing remote archives...');
        $remoteManager->removeArchives();
    }
}
