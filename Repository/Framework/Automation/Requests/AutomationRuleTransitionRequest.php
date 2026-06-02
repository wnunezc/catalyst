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

namespace Catalyst\Repository\Automation\Requests;

use Catalyst\Framework\Http\FormRequest;

/**
 * Defines the Automation Rule Transition Request class contract.
 *
 * @package Catalyst\Repository\Automation\Requests
 * Responsibility: Coordinates the automation rule transition request behavior within its module boundary.
 */
final class AutomationRuleTransitionRequest extends FormRequest
{
    /**
     * @return string[]
     */
    public function only(): array
    {
        return ['transition', 'notes'];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'transition' => 'required',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'transition' => __('automation.show.workflow.transition'),
        ];
    }

    /**
     * Handles the validation message workflow.
     */
    public function validationMessage(): string
    {
        return __('automation.messages.select_transition');
    }

    /**
     * Determines whether has Transition.
     */
    public function hasTransition(): bool
    {
        return $this->transition() !== '';
    }

    /**
     * Handles the transition workflow.
     */
    public function transition(): string
    {
        return trim((string) $this->input('transition', ''));
    }

    /**
     * Handles the notes workflow.
     */
    public function notes(): ?string
    {
        return trim((string) $this->input('notes', '')) ?: null;
    }
}
