<?php

namespace App\Support\Providers;

use FilesystemIterator;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ModuleCommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadModuleCommands();
        }
    }

    protected function loadModuleCommands(): void
    {
        $basePath = base_path('app/Modules');

        if (! is_dir($basePath)) {
            return;
        }

        $commandPaths = [];

        $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::SELF_FIRST
                    );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir() && $fileInfo->getFilename() === 'Commands') {
                $commandPaths[] = $fileInfo->getPathname();
            }
        }

        // si on a trouvé des dossiers Commands -> récupérer les classes et les enregistrer
        if (! empty($commandPaths)) {
            $classes = collect($commandPaths)
                        ->flatMap(fn ($path) => $this->getCommandClasses($path))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

            if (!empty($classes)) {
                $this->commands($classes);
            }
        }
    }

    /**
     * Récupère toutes les classes PHP (récursivement) d'un dossier donné.
     */
    protected function getCommandClasses(string $path): array
    {
        $classes = [];

        $filesIterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::LEAVES_ONLY
                        );

        foreach ($filesIterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $class = $this->getClassFullNameFromFile($file->getPathname());
                if ($class && class_exists($class)) {
                    $classes[] = $class;
                }
            }
        }

        return $classes;
    }

    /**
     * Extrait namespace + nom de classe depuis un fichier PHP.
     */
    protected function getClassFullNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);

        if (! $content) {
            return null;
        }

        $namespace = null;
        $class = null;

        if (preg_match('/namespace\s+([^;]+);/m', $content, $m)) {
            $namespace = trim($m[1]);
        }

        // matches "class Name" or "abstract class Name" or "final class Name"
        if (preg_match('/class\s+([^\s{]+)/m', $content, $m2)) {
            $class = trim($m2[1]);
        }

        if ($namespace && $class) {
            return $namespace . '\\' . $class;
        }

        return null;
    }
}
