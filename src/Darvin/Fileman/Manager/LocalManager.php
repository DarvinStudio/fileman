<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Manager;

use Darvin\Fileman\Archiver\ArchiverInterface;
use Darvin\Fileman\Directory\DirectoryFetcherInterface;

/**
 * Local manager
 */
class LocalManager extends AbstractManager implements LocalManagerInterface
{
    /**
     * @var \Darvin\Fileman\Archiver\ArchiverInterface
     */
    private $archiver;

    /**
     * @var string[]
     */
    private $filesToRemove;

    /**
     * @param \Darvin\Fileman\Directory\DirectoryFetcherInterface $dirFetcher  Directory fetcher
     * @param string                                              $projectPath Project path
     * @param \Darvin\Fileman\Archiver\ArchiverInterface          $archiver    Archiver
     */
    public function __construct(DirectoryFetcherInterface $dirFetcher, string $projectPath, ArchiverInterface $archiver)
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
     * {@inheritDoc}
     */
    public function archiveFiles(callable $callback): array
    {
        $archiveFilenames = [];

        foreach ($this->getDirs() as $param => $dir) {
            $filename = $this->nameArchive($dir, 'local');

            if (!$this->archiver->archive($this->getProjectPath().$dir, $this->getProjectPath().$filename)) {
                continue;
            }

            $callback($filename);

            $archiveFilenames[$param] = $this->filesToRemove[] = $filename;
        }

        return $archiveFilenames;
    }

    /**
     * {@inheritDoc}
     */
    public function extractFiles(callable $callback, array $archiveFilenames): void
    {
        foreach ($this->getDirs() as $param => $dir) {
            if (!isset($archiveFilenames[$param])) {
                continue;
            }

            $filename = $archiveFilenames[$param];

            $this->archiver->extract($this->getProjectPath().$filename, $this->getProjectPath().$dir);

            $callback($filename);

            $this->filesToRemove[] = $filename;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function readConfiguration(): string
    {
        $pathname = $this->getProjectPath().'.env';

        $content = @file_get_contents($pathname);

        if (false !== $content) {
            return $content;
        }

        $pathname = $this->getProjectPath().'app/config/parameters.yml';

        $content = @file_get_contents($pathname);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Unable to read configuration file "%s".', $pathname));
        }

        return $content;
    }
}
