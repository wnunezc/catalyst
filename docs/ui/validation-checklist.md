# Checklist de validacion del parche visual

## Objetivo

Registrar una rutina minima y repetible para verificar que el parche visual documentado no rompa sintaxis, autoload, CLI, CSP ni superficies administrativas clave.

## PowerShell

### Version de PHP

```powershell
php -v
```

### Autoload

```powershell
composer dump-autoload -o
```

### CLI base

```powershell
php public/cli.php help
php public/cli.php security:check
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php route:list
```

### Lint recursivo de PHP

```powershell
Get-ChildItem -Path app,Repository,boot-core,public -Recurse -Include *.php |
    ForEach-Object {
        php -l $_.FullName
    }
```

### Busqueda de inline JS/CSS problematico

```powershell
Get-ChildItem -Path boot-core,Repository,app,public -Recurse -Include *.php,*.phtml,*.html |
    Select-String -Pattern 'onchange=','onclick=','javascript:','style="' |
    Select-Object Path, LineNumber, Line
```

### Verificacion de sincronizacion entre CSS fuente y CSS publicado

```powershell
$pairs = @(
    @{ Source = 'Repository/App/Surface/Dashboard/front/style.css'; Target = 'public/assets/css/work/dashboard/style.css' },
    @{ Source = 'Repository/Framework/Configuration/front/style.css'; Target = 'public/assets/css/work/configuration/style.css' },
    @{ Source = 'Repository/Framework/Operations/front/style.css'; Target = 'public/assets/css/work/operations/style.css' },
    @{ Source = 'Repository/Framework/Roles/front/style.css'; Target = 'public/assets/css/work/roles/style.css' },
    @{ Source = 'Repository/Framework/Automation/front/style.css'; Target = 'public/assets/css/work/automation/style.css' },
    @{ Source = 'Repository/Framework/Catalogs/front/style.css'; Target = 'public/assets/css/work/catalogs/style.css' },
    @{ Source = 'Repository/Framework/Documents/front/style.css'; Target = 'public/assets/css/work/documents/style.css' },
    @{ Source = 'Repository/Framework/Media/front/style.css'; Target = 'public/assets/css/work/media/style.css' },
    @{ Source = 'Repository/Framework/DevTools/front/style.css'; Target = 'public/assets/css/work/devtools/style.css' }
)

$pairs | ForEach-Object {
    $sourceHash = (Get-FileHash $_.Source -Algorithm SHA256).Hash
    $targetHash = (Get-FileHash $_.Target -Algorithm SHA256).Hash
    [PSCustomObject]@{
        Source = $_.Source
        Target = $_.Target
        Match = ($sourceHash -eq $targetHash)
    }
}
```

### Superficies clave a revisar manualmente

```powershell
@(
    '/',
    '/login',
    '/dashboard',
    '/configuration/environment-setup',
    '/configuration/application-health',
    '/configuration/feature-flags',
    '/configuration/plugins',
    '/configuration/platform-appearance',
    '/operations/api-management',
    '/audit-log',
    '/roles',
    '/permissions',
    '/catalogs'
)
```

## Bash

### Version de PHP

```bash
php -v
```

### Autoload

```bash
composer dump-autoload -o
```

### CLI base

```bash
php public/cli.php help
php public/cli.php security:check
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php route:list
```

### Lint recursivo de PHP

```bash
find app Repository boot-core public -name '*.php' -type f -print0 | xargs -0 -n1 php -l
```

### Busqueda de inline JS/CSS problematico

```bash
grep -RIn 'onchange=\|onclick=\|javascript:\|style="' app Repository boot-core public \
    --include='*.php' \
    --include='*.phtml' \
    --include='*.html'
```

## Criterios de lectura de resultados

### Fallo real del proyecto

Debe clasificarse como fallo real si:

- `php -l` reporta error de sintaxis;
- `composer dump-autoload -o` no completa;
- `security:check` devuelve hallazgos relevantes nuevos;
- `route:lint` o `inspect:lint` fallan;
- aparecen `onclick`, `onchange`, `javascript:` o `style=""` en templates runtime ligados al parche.

### Limitacion del entorno

Debe clasificarse como limitacion del entorno si:

- falta `composer` o `php` en la maquina;
- el servidor local no esta disponible para validar superficies en browser;
- no estan presentes los ZIP historicos intermedios del parche.

### Validacion no ejecutada

Debe declararse como no ejecutada si:

- la revision visual interactiva en browser no se hizo en la sesion;
- no existe evidencia local suficiente para atribucion historica archivo por archivo.

## Recomendacion operativa

Al cerrar una iteracion visual:

1. correr primero validaciones de sintaxis y CLI;
2. revisar CSP e inline handlers;
3. verificar superficies clave;
4. confirmar que los CSS publicados sigan sincronizados con sus fuentes;
5. documentar claramente que se valido y que no.
