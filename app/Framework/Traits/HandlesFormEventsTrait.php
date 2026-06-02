<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Traits;

use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;

/**
 * HandlesFormEventsTrait — Event-driven form routing for controllers
 *
 * Implements the LH-Framework "Back/Event" pattern adapted to Catalyst's MVC:
 * A POST request carries a hidden `_event` field whose value maps to a
 * protected `on{EventName}()` method on the controller.
 *
 * Usage:
 *   class UserController extends Controller {
 *       use HandlesFormEventsTrait;
 *
 *       // Route POST /users/store → this method
 *       public function store(): Response {
 *           return $this->dispatchEvent();
 *       }
 *
 *       protected function onSave(): JsonResponse {
 *           // handle save event
 *           return $this->jsonSuccess(['saved' => true], 'User saved.');
 *       }
 *
 *       protected function onDelete(): JsonResponse {
 *           // handle delete event
 *           return $this->jsonSuccess(null, 'User deleted.');
 *       }
 *   }
 *
 * HTML side (with Catalyst FormHandler JS):
 *   <form data-catalyst="form" action="/users/store" method="POST">
 *       <button type="submit" data-event="save">Save</button>
 *       <button type="submit" data-event="delete">Delete</button>
 *   </form>
 *   or with hidden field:
 *   <input type="hidden" name="_event" value="save">
 *
 * Unlike LH-Framework's silent failure, an unknown event returns a 400 JSON
 * response instead of silently doing nothing.
 *
 * @package Catalyst\Framework\Traits
 */
trait HandlesFormEventsTrait
{
    /**
     * Dispatch the incoming POST event to the appropriate handler method.
     *
     * Reads `_event` from POST input and calls `on{EventName}()` on `$this`.
     * Event name is ucfirst'd: event "saveUser" → method "onSaveUser()".
     *
     * @return Response
     */
    protected function dispatchEvent(): Response
    {
        $event = $this->input('_event');

        if ($event === null || $event === '') {
            return $this->onDefault();
        }

        $event  = (string) $event;
        $method = 'on' . ucfirst($event);

        if (!method_exists($this, $method)) {
            return JsonResponse::error("Unknown event: {$event}", null, 400);
        }

        return $this->{$method}();
    }

    /**
     * Default handler — called when the POST request has no `_event` field.
     * Override in the controller to handle plain (event-less) POST requests.
     *
     * @return Response
     */
    protected function onDefault(): Response
    {
        return JsonResponse::error('No event specified.', null, 400);
    }

    /**
     * Return the current event name, or null if none was sent.
     *
     * @return string|null
     */
    protected function eventName(): ?string
    {
        $event = $this->input('_event');
        return ($event !== null && $event !== '') ? (string) $event : null;
    }
}
