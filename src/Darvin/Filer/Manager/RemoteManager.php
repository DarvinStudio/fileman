<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Filer\Manager;

use Darvin\Filer\Directory\DirectoryFetcher;
use Darvin\Filer\SSH\SSHClient;

/**
 * Remote manager
 */
class RemoteManager
{
    /**
     * @var \Darvin\Filer\Directory\DirectoryFetcher
     */
    private $dirFetcher;

    /**
     * @var \Darvin\Filer\SSH\SSHClient
     */
    private $sshClient;

    /**
     * @var string
     */
    private $projectPath;

    /**
     * @var string[]|null
     */
    private $dirs;

    /**
     * @param \Darvin\Filer\Directory\DirectoryFetcher $dirFetcher  Directory fetcher
     * @param \Darvin\Filer\SSH\SSHClient              $sshClient   SSH client
     * @param string                                   $projectPath Project path
     */
    public function __construct(DirectoryFetcher $dirFetcher, SSHClient $sshClient, $projectPath)
    {
        $this->dirFetcher = $dirFetcher;
        $this->sshClient = $sshClient;
        $this->projectPath = $projectPath;

        $this->dirs = null;
    }

    public function archiveFiles()
    {
        $this->getDirs();
    }

    /**
     * @return string[]
     */
    private function getDirs()
    {
        if (null === $this->dirs) {
            $yaml = $this->sshClient->exec(sprintf('cat %s/app/config/parameters.yml', $this->projectPath));

            $this->dirs = $this->dirFetcher->fetchDirectories($yaml);
        }

        return $this->dirs;
    }
}
