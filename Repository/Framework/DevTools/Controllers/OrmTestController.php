<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * OrmTestController — Etapa 9: ORM / Entities tests.
 * Exercises: Model, Collection, Pagination, dirty tracking, casts, $hidden.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Helpers\Exceptions\ModelNotFoundException;
use Catalyst\Repository\Auth\Models\User;
use Catalyst\Repository\DevTools\Models\DemoEmail;

class OrmTestController extends Controller
{
    private function uniqueOrmEmail(string $prefix): string
    {
        return sprintf('%s-%s@catalyst.test', $prefix, bin2hex(random_bytes(6)));
    }

    public function ormStatus(): JsonResponse
    {
        try {
            $all  = DemoEmail::all();
            $page = DemoEmail::query()->paginate(3);

            return $this->jsonSuccess([
                'total'        => $all->count(),
                'emails'       => $all->pluck('email')->all(),
                'is_empty'     => $all->isEmpty(),
                'first_record' => $all->first()?->toArray(),
                'pagination'   => [
                    'items'    => $page->items->toArray(),
                    'total'    => $page->total,
                    'per_page' => $page->perPage,
                    'current'  => $page->currentPage,
                    'last'     => $page->lastPage,
                    'has_more' => $page->hasMorePages(),
                ],
            ], __('devtools.orm_runtime.status_ok'));
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.orm_runtime.error_prefix') . $e->getMessage());
        }
    }

    public function ormCreate(): JsonResponse
    {
        try {
            $record = DemoEmail::create(['email' => $this->uniqueOrmEmail('orm-test')]);

            return $this->jsonSuccess([
                'id'       => $record->getKey(),
                'email'    => $record->email,
                'exists'   => $record->exists(),
                'is_dirty' => $record->isDirty(),
            ], __('devtools.orm_runtime.record_created'));
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.orm_runtime.error_prefix') . $e->getMessage());
        }
    }

    public function ormUpdate(): JsonResponse
    {
        try {
            $record = DemoEmail::query()
                ->where('email', 'LIKE', 'orm-test-%')
                ->orderBy('id', 'DESC')
                ->first();

            if ($record === null) {
                return $this->jsonError(__('devtools.orm_runtime.create_first'), 404);
            }

            $oldEmail        = $record->email;
            $record->email   = $this->uniqueOrmEmail('orm-updated');
            $dirtyBeforeSave = $record->getDirty();
            $record->save();

            return $this->jsonSuccess([
                'id'                => $record->getKey(),
                'old_email'         => $oldEmail,
                'new_email'         => $record->email,
                'dirty_before_save' => $dirtyBeforeSave,
                'is_dirty_after'    => $record->isDirty(),
                'was_changed'       => array_key_exists('email', $dirtyBeforeSave),
            ], __('devtools.orm_runtime.record_updated'));
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.orm_runtime.error_prefix') . $e->getMessage());
        }
    }

    public function ormDeleteLatest(): JsonResponse
    {
        try {
            $record = DemoEmail::query()
                ->where('email', 'LIKE', 'orm-%@catalyst.test')
                ->orderBy('id', 'DESC')
                ->first();

            if ($record === null) {
                return $this->jsonError(__('devtools.orm_runtime.no_record_to_delete'), 404);
            }

            $id    = $record->getKey();
            $email = $record->email;
            $record->delete();

            return $this->jsonSuccess([
                'deleted_id'    => $id,
                'deleted_email' => $email,
                'exists_after'  => $record->exists(),
            ], __('devtools.orm_runtime.record_deleted'));
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.orm_runtime.error_prefix') . $e->getMessage());
        }
    }

    public function ormFindOrFail(): JsonResponse
    {
        try {
            DemoEmail::findOrFail(999999);
            return $this->jsonError(__('devtools.orm_runtime.expected_exception_missing'), 500);
        } catch (ModelNotFoundException $e) {
            return $this->jsonSuccess([
                'exception'   => ModelNotFoundException::class,
                'model_class' => $e->getModelClass(),
                'id'          => $e->getId(),
                'message'     => $e->getMessage(),
            ], __('devtools.orm_runtime.exception_caught'));
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.orm_runtime.unexpected_error_prefix') . $e->getMessage());
        }
    }

    public function ormUserDemo(): JsonResponse
    {
        try {
            $all   = User::all();
            $first = $all->first();

            if ($first === null) {
                return $this->jsonError(__('devtools.orm_runtime.no_users_found'), 404);
            }

            $arr = $first->toArray();

            return $this->jsonSuccess([
                'user_count'         => $all->count(),
                'first_user'         => $arr,
                'password_in_array'  => array_key_exists('password', $arr),
                'active_is_bool'     => is_bool($first->getAttribute('active')),
                'created_at_class'   => $first->getAttribute('created_at') !== null
                                            ? get_class($first->getAttribute('created_at'))
                                            : null,
                'emails'             => $all->pluck('email')->all(),
                'active_users_count' => $all->where('active', true)->count(),
            ], __('devtools.orm_runtime.user_demo_ok'));
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.orm_runtime.error_prefix') . $e->getMessage());
        }
    }
}
