<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Archiver;

use Symfony\Component\Finder\Finder;

/**
 * Archiver
 */
class Archiver
{
    /**
     * @var \ZipArchive
     */
    private $zip;

    /**
     * Archiver constructor.
     */
    public function __construct()
    {
        $this->zip = new \ZipArchive();
    }

    /**
     * @param string $dir      Directory
     * @param string $pathname Archive pathname
     *
     * @throws \RuntimeException
     */
    public function archive($dir, $pathname)
    {
        if (true !== $this->zip->open($pathname, \ZipArchive::CREATE)) {
            throw new \RuntimeException(sprintf('Unable to create archive "%s".', $pathname));
        }
        try {
            $finder = (new Finder())->in($dir);
        } catch (\InvalidArgumentException $ex) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exist.', $dir));
        }
        /** @var \Symfony\Component\Finder\SplFileInfo $dir */
        foreach ($finder->directories() as $dir) {
            if (!$this->zip->addEmptyDir($dir->getRelativePathname())) {
                throw new \RuntimeException(
                    sprintf('Unable to create directory "%s" in archive "%s".', $dir->getRelativePathname(), $pathname)
                );
            }
        }
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files() as $file) {
            if (!$this->zip->addFile($file->getPathname(), $file->getRelativePathname())) {
                throw new \RuntimeException(
                    sprintf('Unable to add file "%s" to archive "%s".', $file->getPathname(), $pathname)
                );
            }
        }
        if (!$this->zip->close()) {
            throw new \RuntimeException(sprintf('Unable to close archive "%s".', $pathname));
        }
    }

    /**
     * @param string $pathname Archive pathname
     * @param string $dir      Directory
     *
     * @throws \RuntimeException
     */
    public function extract($pathname, $dir)
    {
        if (true !== $this->zip->open($pathname)) {
            throw new \RuntimeException(sprintf('Unable to open archive "%s".', $pathname));
        }
        if (!$this->zip->extractTo($dir)) {
            throw new \RuntimeException(sprintf('Unable to extract files from archive "%s" to directory "%s".', $pathname, $dir));
        }
        if (!$this->zip->close()) {
            throw new \RuntimeException(sprintf('Unable to close archive "%s".', $pathname));
        }
    }
}
