@servers(['web' => 'ecoprom@62.109.6.78'])

@task('deploy')
    cd web/avito-alert.ru/public_html
    git pull
    composer install -o --no-dev
@endtask
