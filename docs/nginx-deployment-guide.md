# Гайд по запуску Easy Quiz под Nginx и PHP-FPM

Этот документ описывает запуск приложения Easy Quiz на сервере с Nginx, PHP-FPM и каталогом проекта `/home/deploy/quiz`. Инструкция собрана для окружения Ubuntu/Debian, но подойдёт и для других дистрибутивов с минимальными изменениями.

## 1. Что должно быть установлено

1. PHP 8.1+ с модулями `php-fpm`, `php-json` и `php-mbstring`.
2. Nginx.
3. Пользователь, от имени которого работает PHP-FPM (по умолчанию `www-data`).

Публичные файлы лежат в каталоге `public/`, а служебный код и данные (`lib/`, `data/`) находятся выше и не должны быть доступны напрямую из браузера.【F:README.md†L9-L36】

## 2. Структура каталогов и права доступа

Код проекта лежит в `/home/deploy/quiz`. Создайте каталоги и выдайте права так, чтобы веб-сервер мог читать PHP-файлы и записывать данные квизов:

```bash
sudo chown -R deploy:www-data /home/deploy/quiz
sudo find /home/deploy/quiz -type d -exec chmod 750 {} +
sudo find /home/deploy/quiz -type f -exec chmod 640 {} +
```

Каталог `data/` должен быть доступен на запись пользователю PHP-FPM. Проще всего назначить владельцем пользователя из пула FPM (например, `www-data`) и дать права на запись:

```bash
sudo chown -R www-data:www-data /home/deploy/quiz/data
sudo chmod -R 770 /home/deploy/quiz/data
```

Если вам нужен доступ к файлам как пользователю `deploy`, добавьте его в группу `www-data` или используйте ACL:

```bash
sudo usermod -aG www-data deploy
# или
sudo setfacl -m u:deploy:rwx /home/deploy/quiz/data
```

Проверить, что PHP действительно может создать подкаталог в `data/quizzes`, можно командой:

```bash
sudo -u www-data php -r "require '/home/deploy/quiz/lib/helpers.php'; save_json('/home/deploy/quiz/data/quizzes/test/metadata.json', ['ok' => true]);"
```

## 3. Конфигурация Nginx

Разместите виртуальный хост, который указывает корнем каталог `/home/deploy/quiz/public` и проксирует PHP-файлы в PHP-FPM. Базовая конфигурация приведена ниже (подставьте нужный путь к сокету `fastcgi_pass`):

```nginx
server {
    listen 127.0.0.1:8080 default_server;
    listen [::1]:8080 default_server;

    root /home/deploy/quiz/public;
    index index.php index.html index.htm;

    server_name _;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~* ^/(data|lib|vendor|composer\.json|composer\.lock) {
        deny all;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~* \.(json|lock|env|ini|log)$ {
        deny all;
    }
}
```

После правки перезагрузите Nginx и PHP-FPM:

```bash
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
```

## 4. Как отлавливать ошибки и смотреть логи

* **Nginx** — `/var/log/nginx/access.log` и `/var/log/nginx/error.log`. Для оперативного просмотра используйте `sudo tail -f /var/log/nginx/error.log`.
* **PHP-FPM** — `journalctl -u php8.2-fpm` или файл `/var/log/php8.2-fpm.log` (путь зависит от дистрибутива). Ошибки PHP, в том числе права на файловую систему, будут отображаться здесь.
* **Ошибки приложения** — приложение пишет данные через `save_json()` в `lib/helpers.php`. Если каталог не создаётся, PHP вызовет предупреждение в error-log, а квиз не появится в `data/quizzes`.

Чтобы включить более подробный вывод PHP на время диагностики, в конфигурации пула `/etc/php/8.2/fpm/pool.d/www.conf` временно задайте:

```
php_flag[display_errors] = on
php_admin_value[error_log] = /var/log/php8.2-fpm.log
```

После отладки верните `display_errors = off`.

## 5. Проверка, почему квизы не появляются в `data/quizzes`

1. Убедитесь, что каталог существует и доступен:
   ```bash
   ls -ld /home/deploy/quiz/data /home/deploy/quiz/data/quizzes
   sudo -u www-data touch /home/deploy/quiz/data/quizzes/.perm-check
   ```
   Если команда `touch` завершилась с ошибкой «Permission denied», исправьте права согласно разделу 2.
2. Проверьте свободное место на диске (`df -h`) и квоту пользователя.
3. Посмотрите php-лог на наличие ошибок `file_put_contents` или `mkdir`.
4. Откройте `create.php`, попробуйте создать квиз и убедитесь, что после сохранения в каталоге появился подкаталог `quiz_...` с файлами `metadata.json`, `participants.json`, `responses.json`.

## 6. Ручной запуск и тестирование

1. Перейдите по адресу `http://<ваш-хост>:8080/` — откроется страница `public/index.php` с описанием сервиса.【F:README.md†L38-L59】
2. Создайте квиз на `http://<ваш-хост>:8080/create.php`.
3. После сохранения вы будете перенаправлены на `host.php?id=...`, где появится QR-код и кнопки управления квизом.
4. Для теста клиента откройте `quiz.php?id=...` в другой вкладке или устройстве и отправьте ответы.
5. Используйте `export_csv.php?id=...` для выгрузки результатов.

Если все шаги выполняются без ошибок, конфигурация настроена верно.

## 7. Дополнительные рекомендации

* Автоматизируйте развёртывание через systemd-юнит или Ansible и задокументируйте права доступа (см. `docs/linux-permissions.md`).
* Настройте резервное копирование каталога `/home/deploy/quiz/data` — в нём хранятся все созданные квизы и ответы студентов.
* Для HTTPS заверните upstream на 8080 в обратный прокси, который слушает 443 и передаёт трафик на Nginx, описанный выше.
