<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Deployer;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

require 'recipe/symfony4.php';

// Parse the deployment configuration file
inventory('config/deploy.yaml');
$settings = Yaml::parseFile('config/deploy.yaml');
foreach ($settings['.settings'] as $key => $value) {
    set($key, $value);
}

// If there's a custom deployment script include it here.
$app = get('application');
$customFile = 'deploy.{$app}.php';
if (file_exists($customFile)) {
    require $customFile;
}

set('console', fn() => parse('{{bin/php}} {{release_path}}/bin/console --no-interaction --quiet'));
set('lock_path', fn() => parse('{{deploy_path}}/.dep/deploy.lock'));

/*
 * Check that there are no modified files or commits that haven't been pushed. Ask the
 * user to confirm.
 */
task('dhil:precheck', function() : void {
    $out = runLocally('git status --porcelain --untracked-files=no');
    if ('' !== $out) {
        $modified = count(explode("\n", $out));
        writeln("<error>Warning:</error> {$modified} modified files have not been committed.");
        writeln($out);
        $response = askConfirmation('Continue?');
        if ( ! $response) {
            exit;
        }
    }

    $out = runLocally('git cherry -v');
    if ('' !== $out) {
        $commits = count(explode("\n", $out));
        writeln("<error>Warning:</error> {$commits} commits not pushed.");
        $response = askConfirmation('Continue?');
        if ( ! $response) {
            exit;
        }
    }

    $res = run('[ -f {{lock_path}} ] && echo Locked || echo OK');
    if ('Locked' === trim($res)) {
        writeln('<error>Warning:</error> Target is locked. Unlock and continue?');
        $response = askConfirmation('Continue?');
        if ( ! $response) {
            exit;
        }
        run('rm -f {{lock_path}}');
    }
});

task('dhil:assets', function() : void {
    $output = run('{{console}} assets:install --symlink');
    writeln($output);
})->desc('Install any bundle assets.');

task('dhil:yarn', function() : void {
    $output = run('cd {{ release_path }}/public && yarn install --prod --silent');
    writeln($output);
})->desc('Install yarn dependencies.');

task('dhil:fonts', function() : void {
    if ( ! file_exists('config/fonts.yaml')) {
        return;
    }
    $output = run('cd {{ release_path }} && ./bin/console nines:fonts:download');
    writeln($output);
})->desc('Install fonts if they are configured in config/fonts.yaml');

/*
 * Run the testsuite on the server.
 */
task('dhil:phpunit', function() : void {
    if (file_exists('Makefile')) {
        $output = run('cd {{ release_path }} && make test.db test', ['timeout' => null]);
    } else {
        $output = run('cd {{ release_path }} && ./vendor/bin/phpunit', ['timeout' => null]);
    }
    writeln($output);
})->desc('Run all unit tests');

task('dhil:sphinx:build', function() : void {
    if (file_exists('docs')) {
        runLocally('sphinx-build docs/source public/docs/sphinx');
    }
})->desc('Build sphinx docs locally.');

task('dhil:sphinx:upload', function() : void {
    if (file_exists('docs')) {
        $user = get('user');
        $host = get('hostname');
        $become = get('become');
        within('{{release_path}}', function() : void {
            run('mkdir -p public/docs/sphinx');
        });
        runLocally("rsync -av --rsync-path='sudo -u {$become} rsync' ./public/docs/sphinx/ {$user}@{$host}:{{release_path}}/public/docs/sphinx", ['timeout' => null]);
    }
})->desc('Upload Sphinx docs to server.');

task('dhil:sphinx', [
    'dhil:sphinx:build',
    'dhil:sphinx:upload',
])->desc('Wrapper around dhil:sphinx:build and dhil:sphinx:upload');

task('dhil:db:backup', function() : void {
    $user = get('user');
    $become = get('become');
    $app = get('application');

    set('become', $user); // prevent sudo -u from failing.
    $date = date('Y-m-d');
    $current = get('release_name');
    $file = "/home/{$become}/{$app}-{$date}-r{$current}.sql";
    run("sudo mysqldump {$app} -r {$file}");
    run("sudo chown {$become} {$file}");
    set('become', $become);
})->desc('Backup the mysql database');

task('dhil:db:schema', function() : void {
    $user = get('user');
    $become = get('become');
    $app = get('application');

    set('become', $user); // prevent sudo -u from failing.
    $file = "/home/{$user}/{$app}-schema.sql";
    run("sudo mysqldump {$app} --flush-logs --no-data -r {$file}");
    run("sudo chown {$user} {$file}");

    download($file, basename($file));
    writeln('Downloaded database dump to ' . basename($file));
    set('become', $become);
})->desc('Make a database schema-only backup and download it.');

option('all-tables', null, InputOption::VALUE_NONE, 'Do not ignore any tables when fetching database.');
task('dhil:db:data', function() : void {
    $user = get('user');
    $become = get('become');
    $app = get('application');

    set('become', $user); // prevent sudo -u from failing.
    $file = "/home/{$user}/{$app}-data.sql";
    $ignore = get('ignore_tables', []);
    if (count($ignore) && ! input()->getOption('all-tables')) {
        $ignoredTables = implode(',', array_map(fn($s) => $app . '.' . $s, $ignore));
        run("sudo mysqldump {$app} --flush-logs --no-create-info -r {$file} --ignore-table={{$ignoredTables}}");
    } else {
        run("sudo mysqldump {$app} --flush-logs --no-create-info -r {$file}");
    }
    run("sudo chown {$user} {$file}");

    download($file, basename($file));
    writeln('Downloaded database dump to ' . basename($file));
    set('become', $become);
})->desc('Make a database data-only backup and download it.');

task('dhil:db:migrate', function() : void {
    $count = (int) runLocally('find migrations -type f -name "*.php" | wc -l');
    if ($count > 1) {
        $options = '--allow-no-migration';
        if ('' !== get('migrations_config')) {
            $options = sprintf('%s --configuration={{release_path}}/{{migrations_config}}', $options);
        }
        run(sprintf('cd {{release_path}} && {{bin/php}} {{bin/console}} doctrine:migrations:migrate %s {{console_options}}', $options));
    } else {
        if (1 === $count) {
            $options = '';
            if ('' !== get('migrations_config')) {
                $options = '--configuration={{release_path}}/{{migrations_config}}';
            }
            run(sprintf('cd {{release_path}} && {{bin/php}} {{bin/console}} doctrine:migrations:rollup %s {{console_options}}', $options));
        } else {
            writeln('No migrations found.');
        }
    }
})->desc('Apply database migrations');

task('dhil:media', function() : void {
    $user = get('user');
    $host = get('hostname');
    $become = get('become');
    runLocally("rsync -av --rsync-path='sudo -u {$become} rsync' {$user}@{$host}:{{release_path}}/data/ data", ['timeout' => null]);
})->desc('Download any uploaded media, assuming it is in /data');

task('dhil:permissions', function() : void {
    $user = get('user');
    $become = get('become');

    set('become', $user); // prevent sudo -u from failing.
    $output = run('cd {{ release_path }} && sudo chcon -R ' . get('context') . ' ' . implode(' ', get('writable_dirs')));
    $output .= run('cd {{ release_path }} && sudo chcon -R unconfined_u:object_r:httpd_log_t:s0 var/log');
    if ($output) {
        writeln($output);
    }

    set('become', $become);
})->desc('Fix selinux permissions');

// Display a success message.
task('success', function() : void {
    $target = get('target');
    $release = get('release_name');
    $host = get('hostname');
    $path = get('site_path');

    writeln("Successfully deployed {$target} release {$release}");
    writeln("Visit http://{$host}{$path} to check.");
})->desc('Show message on successful deployment');

// Create backups of the schema and data and download them.
task('dhil:db:fetch', [
    'dhil:db:schema',
    'dhil:db:data',
])->desc('Wrapper around dhil:db:schema and dhil:db:data');

// Fun a complete deployment
task('deploy', [
    'dhil:precheck',
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',

    'dhil:db:backup',
    'dhil:assets',
    'dhil:phpunit',
    'dhil:db:migrate',
    'dhil:sphinx',
    'dhil:yarn',
    'dhil:fonts',

    'deploy:writable',
    'dhil:permissions',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);
after('deploy:failed', 'deploy:unlock');
after('deploy', 'success');
