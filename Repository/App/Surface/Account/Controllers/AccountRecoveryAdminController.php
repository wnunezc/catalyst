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

namespace App\Surface\Account\Controllers;

use App\Surface\Account\Repositories\AccountRecoveryRepository;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

/**
 * Provides administrative review screens for account recovery requests.
 *
 * @package App\Surface\Account\Controllers
 * Responsibility: Lists support recovery requests, shows request details and records approve/reject decisions.
 */
final class AccountRecoveryAdminController extends Controller
{
    /**
     * Stores the recovery repository used by the admin review actions.
     *
     * Responsibility: Stores the recovery repository used by the admin review actions.
     */
    public function __construct(private readonly AccountRecoveryRepository $repository = new AccountRecoveryRepository())
    {
        parent::__construct();
    }

    /**
     * Renders the latest account recovery requests for admin review.
     *
     * Responsibility: Renders the latest account recovery requests for admin review.
     */
    public function index(Request $request): Response
    {
        return $this->view('account.admin-index', [
            'title' => __('account.admin.index.title'),
            'pageTitle' => __('account.admin.index.title'),
            'recovery_requests' => $this->normalizeRows($this->repository->latestRequests(60)),
        ], 200, 'admin');
    }

    /**
     * Renders one recovery request or redirects when the request is unavailable.
     *
     * Responsibility: Renders one recovery request or redirects when the request is unavailable.
     */
    public function show(Request $request, string $id): Response
    {
        $entry = $this->repository->findRequest((int) $id);
        if ($entry === null) {
            $this->flash()->error(__('account.admin.messages.not_found'));
            return $this->redirect('/admin/account-recovery');
        }

        return $this->view('account.admin-show', [
            'title' => __('account.admin.show.title') . ' #' . (int) $entry['id'],
            'pageTitle' => __('account.admin.show.title'),
            'recovery_request' => $this->normalizeRow($entry),
            'csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        ], 200, 'admin');
    }

    /**
     * Marks a recovery request as approved.
     *
     * Responsibility: Marks a recovery request as approved.
     */
    public function approve(Request $request, string $id): Response
    {
        return $this->review((int) $id, 'approved');
    }

    /**
     * Marks a recovery request as rejected.
     *
     * Responsibility: Marks a recovery request as rejected.
     */
    public function reject(Request $request, string $id): Response
    {
        return $this->review((int) $id, 'rejected');
    }

    /**
     * Persists an admin review decision and flashes the result.
     *
     * Responsibility: Persists an admin review decision and flashes the result.
     */
    private function review(int $id, string $status): Response
    {
        $reviewerId = (int) (AuthManager::getInstance()->id() ?? 0);
        $ok = $this->repository->markReviewed($id, $status, $reviewerId);

        $ok
            ? $this->flash()->success(__('account.admin.messages.review_saved'))
            : $this->flash()->error(__('account.admin.messages.review_failed'));

        return $this->redirect('/admin/account-recovery/' . $id);
    }

    /**
     * Normalizes recovery request rows for the admin index template.
     *
     * Responsibility: Normalizes recovery request rows for the admin index template.
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function normalizeRows(array $rows): array
    {
        return array_map(fn (array $row): array => $this->normalizeRow($row), $rows);
    }

    /**
     * Adds display labels and review state flags to one recovery request row.
     *
     * Responsibility: Adds display labels and review state flags to one recovery request row.
     * @param array<string, mixed> $row
     */
    private function normalizeRow(array $row): array
    {
        $status = (string) ($row['status'] ?? 'pending_support_review');
        $type = (string) ($row['request_type'] ?? 'support_recovery');

        return array_merge($row, [
            'id' => (int) ($row['id'] ?? 0),
            'known_email' => (string) ($row['known_email'] ?? ''),
            'alternate_email' => (string) ($row['alternate_email'] ?? ''),
            'request_type' => $type,
            'request_type_label' => __('account.request_types.' . $type),
            'status' => $status,
            'status_label' => __('account.statuses.' . $status),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'reviewed_at' => (string) ($row['reviewed_at'] ?? ''),
            'message' => (string) ($row['message'] ?? ''),
            'can_review' => in_array($status, ['pending_support_review', 'pending_email_verification'], true),
        ]);
    }
}
