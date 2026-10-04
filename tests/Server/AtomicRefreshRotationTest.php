<?php
declare(strict_types=1);

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\HttpPostMap;
use Raxos\OAuth2\Server\Error\InvalidGrantException;
use Raxos\OAuth2\Server\GrantType\RefreshTokenGrantType;
use Raxos\OAuth2\Server\SecurityProfile;
use RaxosTests\OAuth2\AtomicRefreshStore;
use RaxosTests\OAuth2\RotationClient;

covers(RefreshTokenGrantType::class);

it('allows one concurrent refresh and revokes the entire token family after reuse', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'raxos-refresh-');
    $barrier = $path . '-barrier';
    $factory = new AtomicRefreshStore($path);
    $factory->pdo->exec('PRAGMA journal_mode = WAL');
    $factory->pdo->exec('CREATE TABLE families (id INTEGER PRIMARY KEY, revoked INTEGER)');
    $factory->pdo->exec('CREATE TABLE refresh (token TEXT PRIMARY KEY, family INTEGER, client TEXT, scope TEXT, expires INTEGER, consumed INTEGER, revoked INTEGER)');
    $factory->pdo->exec('CREATE TABLE access (token TEXT PRIMARY KEY, family INTEGER, expires INTEGER, revoked INTEGER)');
    $factory->pdo->exec('INSERT INTO families VALUES (1, 0)');
    $factory->pdo->prepare('INSERT INTO refresh VALUES (?, 1, ?, ?, ?, 0, 0)')->execute(['original', 'client', 'read write', time() + 600]);
    $workers = [];

    try {
        for ($id = 0; $id < 2; ++$id) {
            $process = proc_open([PHP_BINARY, __DIR__ . '/../Support/refresh-worker.php', $path, $barrier, (string)$id], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $workers[] = [$process, $pipes];
        }

        $deadline = microtime(true) + 5;

        while ((!is_file($barrier . '-0') || !is_file($barrier . '-1')) && microtime(true) < $deadline) {
            usleep(10000);
        }
        expect(is_file($barrier . '-0') && is_file($barrier . '-1'))->toBeTrue();
        file_put_contents($barrier, 'go');
        $results = [];

        foreach ($workers as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            expect(proc_close($process))->toBe(0, $error);
            $results[] = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
        }
        $workers = [];

        expect(array_column($results, 'accepted'))->toContain(true, false)
            ->and((int)$factory->pdo->query('SELECT COUNT(*) FROM refresh')->fetchColumn())->toBe(2)
            ->and((int)$factory->pdo->query('SELECT COUNT(*) FROM access')->fetchColumn())->toBe(1)
            ->and((int)$factory->pdo->query('SELECT revoked FROM families')->fetchColumn())->toBe(1)
            ->and((int)$factory->pdo->query('SELECT COUNT(*) FROM refresh WHERE revoked = 0')->fetchColumn())->toBe(0)
            ->and((int)$factory->pdo->query('SELECT COUNT(*) FROM access WHERE revoked = 0')->fetchColumn())->toBe(0);

        $replacement = $factory->pdo->query("SELECT token FROM refresh WHERE token <> 'original'")->fetchColumn();
        $grant = new RefreshTokenGrantType($factory, SecurityProfile::modern());
        expect(fn() => $grant->handle(HttpRequest::create(post: new HttpPostMap(['refresh_token' => $replacement])), new RotationClient()))->toThrow(InvalidGrantException::class);
    } finally {
        foreach ($workers as [$process, $pipes]) {
            proc_terminate($process);

            foreach ($pipes as $pipe) {
                fclose($pipe);
            } proc_close($process);
        }

        foreach (glob($path . '*') as $file) {
            unlink($file);
        }
    }
});
