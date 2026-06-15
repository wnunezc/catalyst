# Composer Configuration

## File: composer.json
**Type**: Dependency Management & Autoloading Configuration
**Location**: Project root
**Status**: ✓ Configured and installed

### Package Information
- **Name**: `catalyst/framework`
- **Type**: project
- **License**: proprietary
- **Distribution version**: defined by `catalyst.json`; Composer does not
  declare a package version because Catalyst is distributed as a project base.

### PHP Requirements
- **Minimum PHP Version**: 8.4
- **Required Extensions**:
    - `ext-mbstring` - Multibyte string support
    - `ext-pdo` - PHP Data Objects
    - `ext-json` - JSON support
    - `ext-fileinfo` - File information
    - `ext-openssl` - OpenSSL support
    - `ext-ctype` - Character type checking

### Dependencies
- **PHPMailer**: `^6.9` — SMTP, DKIM support
- **league/oauth2-client**: `^2.9` — OAuth2 social login
- **cboden/ratchet**: `^0.4` — WebSocket server
- **react/http**: `^1.9` — Async HTTP (required transitively by Ratchet)

### Files Auto-loaded by Composer
```json
{
  "files": [
    "boot-core/global-function/dump-function.php"
  ]
}
```

**Note**: `error-catcher.php` is NOT loaded by Composer. It's loaded explicitly by entry points (index.php, cli.php) BEFORE Composer to ensure critical constants are available.

### PSR-4 Autoloading Configuration
```json
{
  "Catalyst\\": "app/",
  "Catalyst\\Framework\\": "app/Framework/",
  "Catalyst\\Helpers\\": "app/Helpers/",
  "Catalyst\\Repository\\": "Repository/Framework/",
  "App\\": "Repository/App/"
}
```

### Namespace Mappings

| Namespace | Directory | Purpose |
|-----------|-----------|---------|
| `Catalyst\` | `app/` | Root namespace (Kernel) |
| `Catalyst\Framework\` | `app/Framework/` | Core framework components |
| `Catalyst\Helpers\` | `app/Helpers/` | Helper utilities |
| `Catalyst\Repository\` | `Repository/Framework/` | Framework modules with screens |
| `App\` | `Repository/App/` | Developer modules |

> Obsolete mappings removed 2026-04-21: `Catalyst\Assets\` (never used) and `Catalyst\Solution\` (legacy `project/Repository/` tree deleted).

### Composer Scripts

**Post-Update**:
- Regenerates optimized autoloader

### Configuration Options
- `optimize-autoloader`: `true` - Creates class map for faster autoloading
- `platform-check`: `true` - Verifies PHP version and extensions
- `sort-packages`: `true` - Keeps dependencies alphabetically sorted
- `preferred-install`: `dist` - Prefers distribution packages
