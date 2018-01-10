<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Manager;

use Darvin\Fileman\Directory\DirectoryFetcher;
use Darvin\Fileman\SSH\SSHClient;

/**
 * Remote manager
 */
class RemoteManager
{
    /**
     * @var \Darvin\Fileman\Directory\DirectoryFetcher
     */
    private $dirFetcher;

    /**
     * @var \Darvin\Fileman\SSH\SSHClient
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
     * @param \Darvin\Fileman\Directory\DirectoryFetcher $dirFetcher  Directory fetcher
     * @param \Darvin\Fileman\SSH\SSHClient              $sshClient   SSH client
     * @param string                                     $projectPath Project path
     */
    public function __construct(DirectoryFetcher $dirFetcher, SSHClient $sshClient, $projectPath)
    {
        $this->dirFetcher = $dirFetcher;
        $this->sshClient = $sshClient;
        $this->projectPath = $projectPath;

        $this->dirs = null;
    }

    /**
     * @return array
     */
    public function archiveFiles()
    {
        $filenames = [];

        $now = new \DateTimeImmutable();

        foreach ($this->getDirs() as $dir) {
            $dir = trim($dir, DIRECTORY_SEPARATOR);

            $filename = $filenames[] = sprintf('%s_%s.zip', str_replace(DIRECTORY_SEPARATOR, '_', $dir), $now->format('d-m-Y_H-i-s'));

            $command = sprintf(
                'cd %s/web/%s && /usr/bin/env zip -r %s%s .',
                $this->projectPath,
                $dir,
                str_repeat('../', substr_count($dir, DIRECTORY_SEPARATOR) + 2),
                $filename
            );

            $this->sshClient->exec($command);
        }

        return $filenames;
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
