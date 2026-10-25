$files = Get-ChildItem -Recurse -Filter *.php
foreach ($f in $files) {
    Write-Host "=== $($f.FullName) ==="
    & php -l $f.FullName
}
