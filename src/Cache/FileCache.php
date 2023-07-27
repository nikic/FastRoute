<?php
declare(strict_types=1);

namespace FastRoute\Cache;

use Closure;
use FastRoute\Cache;
use RuntimeException;

use function chmod;
use function dirname;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_writable;
use function mkdir;
use function rename;
use function restore_error_handler;
use function set_error_handler;
use function unlink;
use function var_export;

use const LOCK_EX;

final class FileCache implements Cache
{
    private const DIRECTORY_PERMISSIONS = 0775;
    private const FILE_PERMISSIONS = 0664;

    /**
     * This is cached in a local static variable to avoid instantiating a closure each time we need an empty handler
     */
    private static Closure $emptyErrorHandler;

    public function __construct()
    {
        self::$emptyErrorHandler ??= static function (): void {
        };
    }

    /** @inheritdoc */
    public function get(string $key, callable $loader): array
    {
        $result = self::readFileContents($key);

        if ($result !== null) {
            return $result;
        }

        $data = $loader();
        self::writeToFile($key, '<?php return ' . var_export($data, true) . ';');

        return $data;
    }

    /** @return array{0: array<string, array<string, mixed>>, 1: array<string, array<array{regex: string, suffix?: string, routeMap: array<int|string, array{0: mixed, 1: array<string, string>}>}>>}|null */
    private static function readFileContents(string $path): array|null
    {
        // error suppression is faster than calling `file_exists()` + `is_file()` + `is_readable()`, especially because there's no need to error here
        set_error_handler(self::$emptyErrorHandler);
        $value = include $path;
        restore_error_handler();

        if (! is_array($value)) {
            return null;
        }

        return $value;
    }

    private static function writeToFile(string $path, string $content): void
    {
        $directory = dirname($path);

        if (! self::createDirectoryIfNeeded($directory) || ! is_writable($directory)) {
            throw new RuntimeException('The cache directory is not writable "' . $directory . '"');
        }

        set_error_handler(self::$emptyErrorHandler);

        $tmpFile = $path . '.tmp';

        if (file_put_contents($tmpFile, $content, LOCK_EX) === false) {
            restore_error_handler();

            return;
        }

        chmod($tmpFile, self::FILE_PERMISSIONS);

        if (! rename($tmpFile, $path)) {
            unlink($tmpFile);
        }

        restore_error_handler();
    }

    private static function createDirectoryIfNeeded(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        set_error_handler(self::$emptyErrorHandler);
        $created = mkdir($directory, self::DIRECTORY_PERMISSIONS, true);
        restore_error_handler();

        return $created !== false || is_dir($directory);
    }
}
