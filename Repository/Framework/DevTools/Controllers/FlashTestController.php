<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * FlashTestController — Etapa 0: Flash message triggers.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;

class FlashTestController extends Controller
{
    private const array VALID_TYPES = ['success', 'error', 'warning', 'info'];

    public function triggerFlash(string $type): Response
    {
        $type = in_array($type, self::VALID_TYPES, true) ? $type : 'info';
        $this->flash()->add($type, sprintf(__('devtools.flash_runtime.triggered'), $type, date('H:i:s')));
        return $this->redirect('/test-features');
    }

    public function triggerFlashPersistent(string $type): Response
    {
        $type = in_array($type, self::VALID_TYPES, true) ? $type : 'info';
        $this->flash()->addPersistent($type, sprintf(__('devtools.flash_runtime.persistent'), $type, date('H:i:s')));
        return $this->redirect('/test-features');
    }

    public function clearFlash(): Response
    {
        $this->flash()->reset();
        return $this->redirect('/test-features');
    }
}
