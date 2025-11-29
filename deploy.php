<?php

declare(strict_types=1);

namespace Deployer;

require 'recipe/laravel.php';

host('techtrack-staging.fruitcake.dev')
    ->set('labels', ['stage' => 'staging'])
    ->set('hostname', '49.12.240.245')
    ->set('remote_user', 'forge')
    ->set('deploy_path', '/home/forge/techtrack-staging.fruitcake.dev');

set('update_code_strategy', 'clone'); // Keep git history
set('repository', 'git@bitbucket.org:fruitcakestudio/summa-techtrack.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Tasks
desc('NPM Installing packages and building assets');
task('build', function () {
    cd('{{release_path}}');
    run('npm install');
    run('npm run build');
});
after('deploy:vendors', 'build');

desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    run('sudo -n service php8.3-fpm reload');
});

// Hooks
after('artisan:migrate', 'build');
after('deploy:symlink', 'php-fpm:restart');
after('deploy:symlink', 'artisan:horizon:terminate');
after('deploy:failed', 'deploy:unlock');
