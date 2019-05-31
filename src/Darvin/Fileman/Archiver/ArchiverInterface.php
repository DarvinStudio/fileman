<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Archiver;

/**
 * Archiver
 */
interface ArchiverInterface
{
    /**
     * @param string $dir      Directory
     * @param string $pathname Archive pathname
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function archive(string $dir, string $pathname): bool;

    /**
     * @param string $pathname Archive pathname
     * @param string $dir      Directory
     *
     * @throws \RuntimeException
     */
    public function extract(string $pathname, string $dir): void;
}
