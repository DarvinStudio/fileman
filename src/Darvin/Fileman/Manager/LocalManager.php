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

use Darvin\Fileman\Archiver\Archiver;
use Darvin\Fileman\Directory\DirectoryFetcher;

/**
 * Local manager
 */
class LocalManager extends AbstractManager
{
    /**
     * @var \Darvin\Fileman\Archiver\Archiver
     */
    private $archiver;

    /**
     * @var string[]
     */
    private $filesToRemove;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcher $dirFetcher  Directory fetcher
     * @param string                                     $projectPath Project path
     * @param \Darvin\Fileman\Archiver\Archiver          $archiver    Archiver
     */
    public function __construct(DirectoryFetcher $dirFetcher, $projectPath, Archiver $archiver)
    {
        parent::__construct($dirFetcher, $projectPath);

        $this->archiver = $archiver;

        $this->filesToRemove = [];
    }

    /**
     * Removes files.
     */
    public function __destruct()
    {
        foreach ($this->filesToRemove as $filename) {
            @unlink($this->getProjectPath().$filename);
        }
    }

    /**
     * @param callable $callback Success callback
     *
     * @return array
     */
    public function archiveFiles(callable $callback)
    {
        $archiveFilenames = [];

        foreach ($this->getDirs() as $param => $dir) {
            $filename = $this->nameArchive($dir, 'local');

            $this->archiver->archive(sprintf('%sweb/%s', $this->getProjectPath(), $dir), $this->getProjectPath().$filename);

            $callback($filename);

            $archiveFilenames[$param] = $this->filesToRemove[] = $filename;
        }

        return $archiveFilenames;
    }

    /**
     * @param callable $callback         Success callback
     * @param array    $archiveFilenames Archive filenames
     *
     * @return LocalManager
     */
    public function extractFiles(callable $callback, array $archiveFilenames)
    {
        $zip = new \ZipArchive();

        foreach ($this->getDirs() as $param => $dir) {
            $filename = $archiveFilenames[$param];

            $pathname = $this->getProjectPath().$filename;

            if (true !== $zip->open($pathname)) {
                throw new \RuntimeException(sprintf('Unable to open archive "%s" using ZIP.', $pathname));
            }
            if (!$zip->extractTo(sprintf('%sweb/%s', $this->getProjectPath(), $dir))) {
                throw new \RuntimeException(sprintf('Unable to extract files from archive "%s".', $pathname));
            }

            $callback($filename);

            $zip->close();

            $this->filesToRemove[] = $filename;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigurationYaml()
    {
        $pathname = $this->getProjectPath().'app/config/parameters.yml';

        $yaml = @file_get_contents($pathname);

        if (false === $yaml) {
            throw new \RuntimeException(sprintf('Unable to read configuration file "%s".', $pathname));
        }

        return $yaml;
    }
}
