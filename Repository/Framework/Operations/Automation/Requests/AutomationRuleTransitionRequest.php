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

namespace Catalyst\Repository\Operations\Automation\Requests;

use Catalyst\Framework\Http\FormRequest;

/**
 * Normalizes automation workflow transition input.
 *
 * @package Catalyst\Repository\Operations\Automation\Requests
 * Responsibility: Expose and validate the transition key and optional operator notes.
 */
final class AutomationRuleTransitionRequest extends FormRequest
{
    /**
     * Returns transition fields accepted from input.
     *
     * Responsibility: Returns transition fields accepted from input.
     * @return string[]
     */
    public function only(): array
    {
        return ['transition', 'notes'];
    }

    /**
     * Requires a workflow transition key.
     *
     * Responsibility: Requires a workflow transition key.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'transition' => 'required',
        ];
    }

    /**
     * Returns translated labels for transition validation errors.
     *
     * Responsibility: Returns translated labels for transition validation errors.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'transition' => __('automation.show.workflow.transition'),
        ];
    }

    /**
     * Returns the message used when no transition was selected.
     *
     * Responsibility: Returns the message used when no transition was selected.
     */
    public function validationMessage(): string
    {
        return __('automation.messages.select_transition');
    }

    /**
     * Determines whether a non-empty transition key was submitted.
     *
     * Responsibility: Determines whether a non-empty transition key was submitted.
     */
    public function hasTransition(): bool
    {
        return $this->transition() !== '';
    }

    /**
     * Returns the normalized transition key.
     *
     * Responsibility: Returns the normalized transition key.
     */
    public function transition(): string
    {
        return trim((string) $this->input('transition', ''));
    }

    /**
     * Returns normalized optional transition notes.
     *
     * Responsibility: Returns normalized optional transition notes.
     */
    public function notes(): ?string
    {
        return trim((string) $this->input('notes', '')) ?: null;
    }
}
