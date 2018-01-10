<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
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
     * @param string $dir             Directory
     * @param string $archiveFilename Archive filename
     *
     * @throws \RuntimeException
     */
    public function archive($dir, $archiveFilename)
    {
        $zip = new \ZipArchive();

        if (true !== $zip->open($archiveFilename, \ZipArchive::CREATE)) {
            throw new \RuntimeException(sprintf('Unable to create archive "%s".', $archiveFilename));
        }
        try {
            $finder = (new Finder())->in($dir);
        } catch (\InvalidArgumentException $ex) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exist.', $dir));
        }
        /** @var \Symfony\Component\Finder\SplFileInfo $dir */
        foreach ($finder->directories() as $dir) {
            if (!$zip->addEmptyDir($dir->getRelativePathname())) {
                throw new \RuntimeException(
                    sprintf('Unable to create directory "%s" in archive "%s".', $dir->getRelativePathname(), $archiveFilename)
                );
            }
        }
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files() as $file) {
            if (!$zip->addFile($file->getPathname(), $file->getRelativePathname())) {
                throw new \RuntimeException(
                    sprintf('Unable to add file "%s" to archive "%s".', $file->getPathname(), $archiveFilename)
                );
            }
        }
        if (!$zip->close()) {
            throw new \RuntimeException(sprintf('Unable to close archive "%s".', $archiveFilename));
        }
    }
}
