# Catalyst Framework Attachments

`Catalyst\Framework\Attachment` owns reusable resource attachments for media items and generated document artifacts. It keeps app records separate from framework storage and gives apps a policy layer before files are linked to domain resources.

## Runtime Pieces

- `AttachmentManager`: links, replaces, lists and detaches media or document artifacts.
- `AttachmentRepository`: reads tenant-scoped attachment rows and reference counts.
- `AttachmentPolicy`: declares MIME, extension, size, disk, purpose and attachment-type constraints.
- `AttachmentPolicyValidator`: validates media/artifact snapshots before links are created.
- `AttachmentVerificationSigner`: creates tamper-evident tokens that apps can encode into QR verification URLs.

## Private Storage

Use the `runtime` disk for private attachments. The storage manager maps it to `boot-core/storage/runtime` and returns an empty public URL. Policies that set `requirePrivateStorage: true` reject public disks and non-empty public URLs.

Example:

```php
$policy = AttachmentPolicy::privateEvidence();

$attachments->attachMedia(
    resourceKey: 'training-records',
    recordId: $recordId,
    mediaItem: $media,
    purpose: 'evidence',
    attachmentType: 'file',
    policy: $policy
);
```

## Happy Path

1. Request validation accepts an uploaded file or generated artifact request.
2. Media/document service stores the object on an allowed disk.
3. App service chooses an `AttachmentPolicy`.
4. `AttachmentManager` validates MIME, extension, size, purpose, type and private-storage requirements.
5. The attachment row is created under `resource_attachments`.
6. Optional verification URL is generated and encoded by the app into a QR image.

## Sad Path

The policy rejects the operation before the link is created when:

- MIME type is not allowed;
- extension is not allowed;
- file exceeds `maxBytes`;
- disk is not in `allowedDisks`;
- private storage is required but the object has a public URL;
- purpose or attachment type is not part of the policy.

For generated replacements, `AttachmentManager` evaluates replacement metadata before mutating storage when the payload contains enough metadata.

## QR Verification Contract

Catalyst does not require a QR library for the framework contract. `AttachmentVerificationSigner` signs the verification payload and returns a URL-safe token:

```php
$signer = new AttachmentVerificationSigner($secret);
$url = $signer->verificationUrl('https://app.test/verify/document', [
    'resource_key' => 'training-records',
    'record_id' => $recordId,
    'checksum_sha256' => $checksum,
    'expires_at' => time() + 86400,
]);
```

Apps may encode that URL into a QR image using their chosen renderer. Verification endpoints should call `verify()` and then compare the signed checksum/resource data against current storage metadata. To support revocation, pass a checker callback that returns true when the current document or attachment has been archived, detached, revoked or superseded.

## Smoke

Run:

```powershell
php public/cli.php attachments:policy-smoke --json
```

The smoke does not require DB, session or MFA. It validates private PDF acceptance, public storage rejection, oversize rejection, bad MIME rejection, purpose rejection, token verification, tamper rejection, revocation rejection and QR-ready URL generation.
