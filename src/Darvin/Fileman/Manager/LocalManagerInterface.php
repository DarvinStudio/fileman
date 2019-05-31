<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Fileman\Manager;

/**
 * Local manager
 */
interface LocalManagerInterface extends ManagerInterface
{
    /**
     * @param callable $callback Success callback
     *
     * @return array
     */
    public function archiveFiles(callable $callback): array;

    /**
     * @param callable $callback         Success callback
     * @param array    $archiveFilenames Archive filenames
     */
    public function extractFiles(callable $callback, array $archiveFilenames): void;
}
