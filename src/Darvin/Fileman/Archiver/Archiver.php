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
     * @return bool
     * @throws \RuntimeException
     */
    public function archive($dir, $pathname)
    {
        try {
            $finder = (new Finder())->in($dir);
        } catch (\InvalidArgumentException $ex) {
            return false;
        }
        if (0 === $finder->count()) {
            return false;
        }
        if (true !== $this->zip->open($pathname, \ZipArchive::CREATE)) {
            throw new \RuntimeException(sprintf('Unable to create archive "%s".', $pathname));
        }
        /** @var \Symfony\Component\Finder\SplFileInfo $directory */
        foreach ($finder->directories() as $directory) {
            if (!$this->zip->addEmptyDir($directory->getRelativePathname())) {
                throw new \RuntimeException(
                    sprintf('Unable to create directory "%s" in archive "%s".', $directory->getRelativePathname(), $pathname)
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

        return true;
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
