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
class LocalManager
{
    /**
     * @var \Darvin\Fileman\Directory\DirectoryFetcher
     */
    private $dirFetcher;

    /**
     * @var string
     */
    private $projectPath;

    /**
     * @var \Darvin\Fileman\Archiver\Archiver
     */
    private $archiver;

    /**
     * @var array|null
     */
    private $dirs;

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
        if (!empty($projectPath)) {
            $projectPath = rtrim($projectPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }

        $this->dirFetcher = $dirFetcher;
        $this->projectPath = $projectPath;
        $this->archiver = $archiver;

        $this->dirs = null;
        $this->filesToRemove = [];
    }

    /**
     * Removes archives.
     */
    public function __destruct()
    {
        foreach ($this->filesToRemove as $filename) {
            @unlink($this->projectPath.$filename);
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

        $now = new \DateTimeImmutable();

        foreach ($this->getDirs() as $param => $dir) {
            $dir = trim($dir, DIRECTORY_SEPARATOR);

            $filename = sprintf('%s_%s.zip', str_replace(DIRECTORY_SEPARATOR, '_', $dir), $now->format('d-m-Y_H-i-s'));

            $this->archiver->archive(sprintf('%sweb/%s', $this->projectPath, $dir), $this->projectPath.$filename);

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

            $pathname = $this->projectPath.$filename;

            if (true !== $zip->open($pathname)) {
                throw new \RuntimeException(sprintf('Unable to open archive "%s" using ZIP.', $pathname));
            }
            if (!$zip->extractTo(sprintf('%sweb/%s', $this->projectPath, $dir))) {
                throw new \RuntimeException(sprintf('Unable to extract files from archive "%s".', $pathname));
            }

            $callback($filename);

            $zip->close();

            $this->filesToRemove[] = $filename;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getProjectPath()
    {
        return $this->projectPath;
    }

    /**
     * @return array
     */
    private function getDirs()
    {
        if (null === $this->dirs) {
            $pathname = $this->projectPath.'app/config/parameters.yml';

            $yaml = @file_get_contents($pathname);

            if (false === $yaml) {
                throw new \RuntimeException(sprintf('Unable to read configuration file "%s".', $pathname));
            }

            $this->dirs = $this->dirFetcher->fetchDirectories($yaml);
        }

        return $this->dirs;
    }
}
