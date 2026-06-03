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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Attachment\AttachmentPolicy;
use Catalyst\Framework\Attachment\AttachmentPolicyValidator;
use Catalyst\Framework\Attachment\AttachmentVerificationSigner;
use Catalyst\Framework\Cli\AbstractCommand;

/**
 * attachments:policy-smoke CLI command.
 *
 * Responsibility: Runs the attachments:policy-smoke command to exercise private attachment policy and verification tokens.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class AttachmentsPolicySmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'attachments:policy-smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Exercise private attachment policy validation and QR-ready verification tokens';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Coordinates the smoke scenario and returns a process exit code without hidden side effects.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $policy = AttachmentPolicy::privateEvidence(1024);
        $validator = new AttachmentPolicyValidator();
        $signer = new AttachmentVerificationSigner('attachment-policy-smoke-secret');

        $privatePdf = [
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 512,
            'disk' => 'runtime',
            'public_url' => '',
        ];
        $publicPdf = $privatePdf;
        $publicPdf['disk'] = 'local';
        $publicPdf['public_url'] = '/generated-documents/public.pdf';
        $oversized = $privatePdf;
        $oversized['size_bytes'] = 2048;
        $badType = $privatePdf;
        $badType['mime_type'] = 'application/x-msdownload';
        $badType['extension'] = 'exe';

        $token = $signer->sign([
            'resource_key' => 'framework.attachments.smoke',
            'record_id' => 123,
            'checksum_sha256' => hash('sha256', 'payload'),
            'expires_at' => time() + 3600,
        ]);
        $verified = $signer->verify($token);
        $tampered = $signer->verify($token . 'x');
        $revoked = $signer->verify($token, revocationChecker: static fn (array $payload): bool => true);
        $url = $signer->verificationUrl('https://example.test/verify/document', [
            'resource_key' => 'framework.attachments.smoke',
            'record_id' => 123,
        ]);

        $steps = [
            'private_pdf_allowed' => $validator->validateMedia($privatePdf, $policy, 'evidence', 'file') === [],
            'public_storage_rejected' => $validator->validateMedia($publicPdf, $policy, 'evidence', 'file') !== [],
            'oversized_rejected' => $validator->validateMedia($oversized, $policy, 'evidence', 'file') !== [],
            'bad_type_rejected' => $validator->validateMedia($badType, $policy, 'evidence', 'file') !== [],
            'purpose_rejected' => $validator->validateMedia($privatePdf, $policy, 'avatar', 'file') !== [],
            'token_verified' => is_array($verified) && ($verified['record_id'] ?? null) === 123,
            'tamper_rejected' => $tampered === null,
            'revoked_rejected' => $revoked === null,
            'verification_url_contains_token' => str_contains($url, 'token='),
        ];

        $payload = [
            'success' => !in_array(false, $steps, true),
            'steps' => $steps,
        ];

        if ($json) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->line('Attachments policy smoke: ' . ($payload['success'] ? 'OK' : 'FAILED'));
        }

        return $payload['success'] ? 0 : 1;
    }
}