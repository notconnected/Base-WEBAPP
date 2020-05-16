
- Админитсративная панель: управление действиями, пользователями, группами, правами для групп
- REST API интерфейс
- Логгер в Graylog2
- ORM: DOctrine2 (с миграциями)
- Шаблоны на Twig
- Мультиязычность, переводы в *.po, *.mo, через gettext

# Базовая структура приложения административной части и API

## Пример запросов

- /admin/ - админка
- /publicPart/... - Пример публичного контроллера
- /api/... - Пример публичного API
	- GET yourdomain.ru/api/index = /application/Controllers/Api.php->getActionIndex()
	- POST yourdomain.ru/api/dosomething = /application/Controllers/Api.php->postActionDosomething()
	- ...


## Окружение

`./bootstrap.php` - настройки подключения к БД


Twig кеширует файлы шаблонов в `/application/cache`. Папка должна быть доступна для записи для пользователя nginx

В папке проекта:

1. `composer install`
2. `./vendor/bin/doctrine-migrations diff`
3. `./vendor/bin/doctrine-migrations migrate --no-interaction`



## Первый запуск

1. После создания таблиц в бд необходимо их наполнить, на данный момент миграции для этого не созданы.
2. Закомментируйте вызовы `$this->checkPrivilegesAndDoAction(__FUNCTION__);` в контроллере application\Controllers\Admin.php
3. Перейдите по адресу yourdomain.ru/admin и создайте экшены, пользователей, группы и распределите права доступа
4. Раскомментируйте вызовы `$this->checkPrivilegesAndDoAction(__FUNCTION__);` в контроллере application\Controllers\Admin.php. Enjoy!




## Полезные команды

Запуск миграций:

- `./vendor/bin/doctrine-migrations migrate`

или конкретной миграции:

- `./vendor/bin/doctrine-migrations execute --up 20200401104032`

Создание новой пустой миграции

- `./vendor/bin/doctrine-migrations generate`

После изменения моделей можно сгенерировать миграции для применения изменений в базе

- `./vendor/bin/doctrine-migrations diff`

## Пример конфига хоста nginx

    server {
            listen 80;
            server_name site.local;
            root /var/www/site/public;
            index index.php;

            location ~ \.php$ {
                    fastcgi_index index.php;
                    fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
                    fastcgi_split_path_info ^(.+\.php)(/.+)$;
                    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

                    include fastcgi_params;
            }

            location ~* \.(?:css|js|map|jpe?g|gif|png)$ { }

            location / {
                    try_files $uri $uri/ /index.php?$query_string;
            }
    }
