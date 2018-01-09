<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Filer\Manager;

/**
 * Local manager
 */
class LocalManager
{
    /**
     * @var string
     */
    private $projectPath;

    /**
     * @param string $projectPath Project path
     */
    public function __construct($projectPath)
    {
        $this->projectPath = $projectPath;
    }
}