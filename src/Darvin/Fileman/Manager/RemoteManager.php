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
class RemoteManager extends AbstractManager
{
    /**
     * @var \Darvin\Fileman\SSH\SSHClient
     */
    private $sshClient;

    /**
     * @var array
     */
    private $archiveFilenames;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcher $dirFetcher  Directory fetcher
     * @param string                                     $projectPath Project path
     * @param \Darvin\Fileman\SSH\SSHClient              $sshClient   SSH client
     */
    public function __construct(DirectoryFetcher $dirFetcher, $projectPath, SSHClient $sshClient)
    {
        parent::__construct($dirFetcher, $projectPath);

        $this->sshClient = $sshClient;

        $this->archiveFilenames = [];
    }

    /**
     * @param callable $callback Success callback
     *
     * @return array
     */
    public function archiveFiles(callable $callback)
    {
        foreach ($this->getDirs() as $param => $dir) {
            $filename = $this->nameArchive($dir, $this->sshClient->getHost());

            $dirPathname = sprintf('%sweb/%s', $this->getProjectPath(), $dir);

            $command = sprintf(
                'if [ -n "$(ls -A %s 2>/dev/null)" ]; then cd %1$s && /usr/bin/env zip -r %s%s .; fi',
                $dirPathname,
                str_repeat('../', substr_count($dir, DIRECTORY_SEPARATOR) + 2),
                $filename
            );

            $output = $this->sshClient->exec($command);

            if (empty($output)) {
                continue;
            }

            $callback($filename);

            $this->archiveFilenames[$param] = $filename;
        }

        return $this->archiveFilenames;
    }

    /**
     * @param callable $callback         Success callback
     * @param string   $localProjectPath Local project path
     */
    public function downloadArchives(callable $callback, $localProjectPath)
    {
        foreach ($this->archiveFilenames as $filename) {
            $this->sshClient->get($this->getProjectPath().$filename, $localProjectPath.$filename);

            $callback($filename);
        }
    }

    /**
     * @param callable $callback Success callback
     */
    public function extractFiles(callable $callback)
    {
        foreach ($this->getDirs() as $param => $dir) {
            if (!isset($this->archiveFilenames[$param])) {
                continue;
            }

            $filename = $this->archiveFilenames[$param];

            $command = sprintf('cd %s && /usr/bin/env unzip -o %s -d web/%s', $this->getProjectPath(), $filename, $dir);

            $this->sshClient->exec($command);

            $callback($filename);
        }
    }

    /**
     * @param callable $callback Success callback
     */
    public function removeArchives(callable $callback)
    {
        foreach ($this->archiveFilenames as $param => $filename) {
            $command = sprintf('rm %s%s', $this->getProjectPath(), $filename);

            $this->sshClient->exec($command);

            $callback($filename);

            unset($this->archiveFilenames[$param]);
        }
    }

    /**
     * @param callable $callback         Success callback
     * @param string   $localProjectPath Local project path
     * @param array    $archiveFilenames Archive filenames
     */
    public function uploadArchives(callable $callback, $localProjectPath, array $archiveFilenames)
    {
        foreach ($archiveFilenames as $param => $filename) {
            $pathname = $localProjectPath.$filename;

            if (!is_file($pathname)) {
                continue;
            }

            $this->sshClient->put($pathname, $this->getProjectPath().$filename);

            $callback($filename);

            $this->archiveFilenames[$param] = $filename;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigurationYaml()
    {
        return $this->sshClient->exec(sprintf('cat %sapp/config/parameters.yml', $this->getProjectPath()));
    }
}
