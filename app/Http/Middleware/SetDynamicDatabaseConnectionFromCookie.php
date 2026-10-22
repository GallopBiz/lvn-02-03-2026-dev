<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SetDynamicDatabaseConnectionFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        [$selectedDatabase, $source] = $this->resolveDatabaseSelection($request);

        if ($selectedDatabase !== null) {
            // A dynamic academic session was requested via cookie or session.
            // Strictly validate format, verify physical existence, and authorize user before switching.
            $validatedDatabase = $this->validateVerifyAndAuthorize($request, $selectedDatabase, $source);
            $this->switchDynamicDatabase($validatedDatabase, $source);
        } else {
            // Issue 5: When no academic session is selected, do NOT switch databases.
            // Continue with the existing connection without forcing an unexpected database switch.
            // If an explicit default enforcement is configured, validate it before switching (Issue 4).
            if (Config::get('database.enforce_academic_default', false)) {
                $rawDefault = $this->resolveDefaultAcademicDatabase();
                if ($rawDefault) {
                    $validatedDefault = $this->validateVerifyAndAuthorize($request, $rawDefault, 'default_academic_year');
                    $this->switchDynamicDatabase($validatedDefault, 'default_academic_year');
                }
            }
        }

        return $next($request);
    }

    /**
     * Resolve the database name and source based on application priority:
     * 1. Laravel request cookie 'selectedYear'
     * 2. Session value 'selectedYear'
     * 3. Session value 'db_names'
     *
     * Issue 3: Raw $_COOKIE access has been removed to preserve Laravel's cookie handling & encryption.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array{0: ?string, 1: ?string} [databaseName, source]
     */
    protected function resolveDatabaseSelection(Request $request): array
    {
        // 1. Laravel request cookie 'selectedYear'
        $cookie = $request->cookie('selectedYear');
        if (is_string($cookie) && trim($cookie) !== '') {
            return [trim($cookie), 'request_cookie:selectedYear'];
        }

        // 2. Session value 'selectedYear'
        if ($request->hasSession()) {
            $sessionYear = $request->session()->get('selectedYear');
            if (is_string($sessionYear) && trim($sessionYear) !== '') {
                return [trim($sessionYear), 'session:selectedYear'];
            }
        }

        // 3. Session value 'db_names'
        if ($request->hasSession()) {
            $sessionDbNames = $request->session()->get('db_names');
            if (is_string($sessionDbNames) && trim($sessionDbNames) !== '') {
                return [trim($sessionDbNames), 'session:db_names'];
            }
        }

        return [null, null];
    }

    /**
     * Validate, verify physical existence, and authorize access for a database (Issue 4).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $rawDatabase
     * @param  string  $source
     * @return string Validated and authorized database name
     */
    protected function validateVerifyAndAuthorize(Request $request, string $rawDatabase, string $source): string
    {
        $validatedDatabase = $this->validateAndNormalizeDatabase($rawDatabase);

        if (!$validatedDatabase) {
            Log::warning('Dynamic database connection rejected: invalid database name format.', [
                'source'           => $source,
                'raw_value'        => $rawDatabase,
                'current_database' => Config::get('database.connections.dynamic.database'),
            ]);

            abort(400, "Invalid academic session database requested.");
        }

        // Issue 2: Verify database physically exists on MySQL server (fails closed)
        if (!$this->databaseExists($validatedDatabase)) {
            Log::error('Requested dynamic database does not exist on MySQL server.', [
                'source'           => $source,
                'target_database'  => $validatedDatabase,
                'current_database' => Config::get('database.connections.dynamic.database'),
            ]);

            abort(404, "Academic session database '{$validatedDatabase}' was not found.");
        }

        // Issue 1: Verify user authorization (fails secure - does NOT default to true)
        if (!$this->isUserAuthorizedForDatabase($request, $validatedDatabase)) {
            Log::warning('User unauthorized to access requested dynamic database.', [
                'user_id'         => Auth::id(),
                'source'          => $source,
                'target_database' => $validatedDatabase,
            ]);

            abort(403, "You do not have permission to access the academic session '{$validatedDatabase}'.");
        }

        return $validatedDatabase;
    }

    /**
     * Validate and normalize the database name according to application conventions.
     *
     * @param  string  $rawName
     * @return string|null Normalized valid database name, or null if invalid
     */
    protected function validateAndNormalizeDatabase(string $rawName): ?string
    {
        $name = trim($rawName);

        if ($name === '' || strlen($name) > 64 || !preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) {
            return null;
        }

        // 1. Check if an explicit allowed list is defined in configuration
        $allowed = Config::get('database.allowed_dynamic_databases');
        if (is_array($allowed) && !empty($allowed)) {
            if (in_array($name, $allowed, true)) {
                return $name;
            }
            $normalizedHyphen = str_replace('-', '_', $name);
            if (in_array($normalizedHyphen, $allowed, true)) {
                return $normalizedHyphen;
            }
            return null;
        }

        // 2. Normalize hyphens for academic year convention (e.g. 2025-2026 -> 2025_2026)
        if (preg_match('/^\d{4}-\d{4}$/', $name)) {
            $name = str_replace('-', '_', $name);
        }

        // 3. Verify it matches standard academic session database format (YYYY_YYYY)
        if (preg_match('/^\d{4}_\d{4}$/', $name)) {
            return $name;
        }

        // 4. Disallow system/internal MySQL databases
        $blacklisted = ['information_schema', 'performance_schema', 'mysql', 'sys'];
        if (in_array(strtolower($name), $blacklisted, true)) {
            return null;
        }

        // 5. Allow currently configured default dynamic database name
        $defaultDynamic = Config::get('database.connections.dynamic.database');
        if (is_string($defaultDynamic) && $name === $defaultDynamic) {
            return $name;
        }

        return null;
    }

    /**
     * Verify whether the database exists on the MySQL server (Issue 2).
     * Fails closed: returns false if the query throws an exception.
     *
     * @param  string  $databaseName
     * @return bool
     */
    protected function databaseExists(string $databaseName): bool
    {
        $allowed = Config::get('database.allowed_dynamic_databases');
        if (is_array($allowed) && !empty($allowed)) {
            return in_array($databaseName, $allowed, true);
        }

        try {
            $queryConnection = Config::get('database.dynamic_lookup_connection')
                ?: (Config::get('database.default') ?: 'mysql');
            $exists = DB::connection($queryConnection)
                ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ? LIMIT 1", [$databaseName]);

            return !empty($exists);
        } catch (Throwable $e) {
            // Fail closed: reject database selection if existence verification throws an exception
            Log::error('Dynamic database existence verification failed with exception', [
                'target_database' => $databaseName,
                'error'           => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify if the user is authorized to access the requested database (Issue 1).
     * Fails secure: authenticated users do NOT default to true.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $databaseName
     * @return bool
     */
    protected function isUserAuthorizedForDatabase(Request $request, string $databaseName): bool
    {
        $user = $request->user();
        if (!$user) {
            // Unauthenticated requests (e.g. login pages) select database to authenticate against
            return true;
        }

        // 1. Explicit domain method on user model
        if (method_exists($user, 'canAccessAcademicSession')) {
            return (bool) $user->canAccessAcademicSession($databaseName);
        }

        // 2. Administrative / Academic staff have system-wide access to all valid sessions
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Admin', 'Academic', 'super-admin', 'admin'])) {
            return true;
        }

        // 3. User with assigned session (e.g. Students in student_registration table)
        if (!empty($user->session_name)) {
            $userSession = str_replace('-', '_', trim((string) $user->session_name));
            return $userSession === $databaseName;
        }

        // 4. Configuration-based user permissions if defined
        $allowedSessions = Config::get("database.user_session_permissions.{$user->id}");
        if (is_array($allowedSessions)) {
            return in_array($databaseName, $allowedSessions, true);
        }

        // 5. Fail-secure: Non-admin authenticated user with no matching authorization rule is denied
        return false;
    }

    /**
     * Switch the dynamic database connection with complete rollback and strict failure handling.
     *
     * @param  string  $targetDatabase
     * @param  string  $source
     * @return void
     */
    protected function switchDynamicDatabase(string $targetDatabase, string $source): void
    {
        $previousDatabase = Config::get('database.connections.dynamic.database');
        $previousDefault  = Config::get('database.default');

        // Skip re-switching if already connected to target database
        if ($previousDatabase === $targetDatabase && $previousDefault === 'dynamic') {
            try {
                DB::connection('dynamic')->getPdo();
                return;
            } catch (Throwable $e) {
                // Connection dropped; proceed to reconnect
            }
        }

        // Apply new configuration
        Config::set('database.connections.dynamic.database', $targetDatabase);
        Config::set('database.default', 'dynamic');

        try {
            DB::purge('dynamic');
            DB::reconnect('dynamic');

            // Verify connection works
            DB::connection('dynamic')->getPdo();

            Log::info('Dynamic database switched successfully', [
                'source'            => $source,
                'target_database'   => $targetDatabase,
                'previous_database' => $previousDatabase,
            ]);
        } catch (Throwable $e) {
            // Roll back BOTH database name AND database.default setting
            Config::set('database.connections.dynamic.database', $previousDatabase);
            Config::set('database.default', $previousDefault);
            DB::purge('dynamic');

            try {
                DB::reconnect('dynamic');
            } catch (Throwable $revertException) {
                // Ignore rollback reconnection failure
            }

            Log::critical('Dynamic database connection switch failed; reverted configuration', [
                'source'          => $source,
                'target_database' => $targetDatabase,
                'reverted_to'     => $previousDatabase,
                'error_class'     => get_class($e),
                'error_message'   => $e->getMessage(),
            ]);

            // Abort immediately to prevent cross-tenant data corruption
            abort(503, "Database connection to academic session '{$targetDatabase}' failed. Please contact the administrator.");
        }
    }

    /**
     * Resolve the fallback default academic year database (Issue 4).
     *
     * @return string|null
     */
    protected function resolveDefaultAcademicDatabase(): ?string
    {
        $default = Config::get('database.default_academic_year')
            ?: Config::get('database.connections.dynamic.database')
            ?: env('DB_DATABASE');

        return $default ? (string) $default : null;
    }
}
