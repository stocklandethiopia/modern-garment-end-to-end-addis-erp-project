<?php

declare(strict_types=1);

/**
 * Fail closed when application runtime code or committed environment templates
 * introduce a loopback endpoint. CI, PHPUnit, Compose, the Angular development
 * proxy, and local-development documentation are deliberately outside this
 * scan because they are isolated developer/test infrastructure.
 */

$repositoryRoot = dirname(__DIR__);

$scanPaths = [
    'backend/app',
    'backend/bootstrap',
    'backend/config',
    'backend/routes',
    'backend/.env.example',
    'frontend/src',
];

$extensions = [
    'php',
    'ts',
    'tsx',
    'js',
    'mjs',
    'cjs',
    'json',
    'html',
    'scss',
    'css',
    'yaml',
    'yml',
];

$loopbackPattern = '/(?<![A-Z0-9_])(?:localhost(?:\.localdomain)?|127(?:\.\d{1,3}){3}|::1|0:0:0:0:0:0:0:1)(?![A-Z0-9_])/i';
$violations = [];

foreach ($scanPaths as $relativePath) {
    $absolutePath = $repositoryRoot.DIRECTORY_SEPARATOR.$relativePath;

    if (! file_exists($absolutePath)) {
        continue;
    }

    $files = [];

    if (is_file($absolutePath)) {
        $files[] = $absolutePath;
    } elseif (is_dir($absolutePath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
    }

    foreach ($files as $filePath) {
        $isEnvironmentExample = str_ends_with($filePath, DIRECTORY_SEPARATOR.'.env.example')
            || basename($filePath) === '.env.example';
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (! $isEnvironmentExample && ! in_array($extension, $extensions, true)) {
            continue;
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            fwrite(STDERR, "Unable to read {$filePath}.\n");
            exit(2);
        }

        $relativeFilePath = ltrim(
            substr($filePath, strlen($repositoryRoot)),
            DIRECTORY_SEPARATOR,
        );

        foreach (preg_split('/\R/', $contents) ?: [] as $lineNumber => $line) {
            if (preg_match($loopbackPattern, $line) === 1) {
                $violations[] = sprintf(
                    '%s:%d:%s',
                    $relativeFilePath,
                    $lineNumber + 1,
                    trim($line),
                );
            }
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "FAIL: loopback endpoints are not allowed in runtime source or committed environment templates.\n");

    foreach ($violations as $violation) {
        fwrite(STDERR, "  {$violation}\n");
    }

    fwrite(
        STDERR,
        "Supply service endpoints through environment configuration. Keep loopback values only in isolated CI/test/Compose/developer tooling.\n",
    );

    exit(1);
}

fwrite(STDOUT, "PASS: runtime source and committed environment templates contain no loopback endpoints.\n");
