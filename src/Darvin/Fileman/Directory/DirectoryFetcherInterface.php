<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Directory;

/**
 * Directory fetcher
 */
interface DirectoryFetcherInterface
{
    /**
     * @param string $yaml Configuration YAML
     *
     * @return array
     * @throws \RuntimeException
     */
    public function fetchDirectories(string $yaml): array;
}