sudo bash
cd /var/www
git clone https://github.com/uran-content/easy-quiz
mv ./easy-quiz/* .

## Настройка окружения

1. Убедитесь, что на сервере установлены обновления пакетов:
   ```bash
   apt update && apt upgrade -y
   ```
2. Установите необходимые компоненты: Nginx, PHP 8.2 (или новее) с FPM и базовыми расширениями, а также утилиты для работы с архивами и сертификатами:
   ```bash
   apt install -y nginx php8.2-fpm php8.2-cli php8.2-xml php8.2-mbstring php8.2-zip php8.2-curl unzip certbot python3-certbot-nginx
   ```
   Если в репозитории нет пакетов PHP нужной версии, добавьте PPA `ppa:ondrej/php` и повторите установку.
3. В каталоге `/var/www` создайте отдельного системного пользователя (например, `deploy`), чтобы запускать приложение от его имени:
   ```bash
   adduser --system --ingroup www-data --home /var/www deploy
   chown -R deploy:www-data /var/www
   ```

## Структура проекта и права

1. После копирования файлов убедитесь, что служебные каталоги недоступны напрямую из веба. Публичные файлы живут в `public/`, а PHP-логика и данные — в `lib/` и `data/` выше веб-корня.
2. Проверьте, что FPM-пользователь (`www-data`) может читать PHP-файлы и записывать данные квизов:
   ```bash
   chown -R deploy:www-data /var/www
   find /var/www -type d -exec chmod 750 {} +
   find /var/www -type f -exec chmod 640 {} +
   chown -R www-data:www-data /var/www/data
   chmod -R 770 /var/www/data
   ```
3. Если разработчики будут править код через SSH, добавьте их пользователей в группу `www-data` или настройте ACL:
   ```bash
   usermod -aG www-data <имя_пользователя>
   setfacl -R -m u:<имя_пользователя>:rwx /var/www
   ```

## Конфигурация PHP-FPM

1. Откройте конфигурацию пула PHP (обычно `/etc/php/8.2/fpm/pool.d/www.conf`) и убедитесь, что пользователь и группа указаны как `www-data`, а `listen` ссылается на Unix-сокет `/run/php/php8.2-fpm.sock`.
2. Для production-среды установите параметры логирования и ошибок:
   ```ini
   php_admin_value[error_log] = /var/log/php8.2-fpm.log
   php_flag[display_errors] = off
   php_admin_value[memory_limit] = 256M
   ```
3. Перезапустите PHP-FPM:
   ```bash
   systemctl restart php8.2-fpm
   ```

## Конфигурация Nginx

1. Создайте файл `/etc/nginx/sites-available/easy-quiz`:
   ```nginx
   server {
       listen 80;
       server_name example.com;

       root /var/www/public;
       index index.php index.html;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~* ^/(data|lib|vendor|composer\.(json|lock)) {
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
2. Включите конфигурацию и перезапустите Nginx:
   ```bash
   ln -s /etc/nginx/sites-available/easy-quiz /etc/nginx/sites-enabled/
   nginx -t
   systemctl reload nginx
   ```
3. Для HTTPS получите сертификат через Let’s Encrypt:
   ```bash
   certbot --nginx -d example.com
   ```

## Проверка приложения

1. Откройте `http://example.com/` — должна загрузиться стартовая страница (`public/index.php`).
2. Перейдите на `http://example.com/create.php`, создайте тестовый квиз и убедитесь, что в каталоге `data/quizzes` появились подкаталоги с метаданными.
3. На странице `host.php` запустите квиз, проверьте генерацию QR-кода и обновление статистики.
4. Откройте `quiz.php` в режиме участника, отправьте ответы и убедитесь, что `responses.json` обновляется.
5. Проверьте выгрузку результатов через `export_csv.php` — CSV-файл должен содержать ответы всех участников.

## Обслуживание

1. Настройте регулярное резервное копирование каталога `data/` и конфигураций Nginx.
2. Обновляйте пакеты командой `apt update && apt upgrade -y`, затем перезапускайте PHP-FPM и Nginx.
3. Для развёртывания новых версий повторяйте `git pull`, проверяйте права на `data/` и перезапускайте сервисы.
4. Мониторьте логи: `/var/log/nginx/error.log`, `/var/log/php8.2-fpm.log` и журналы systemd (`journalctl -u nginx -u php8.2-fpm`).
5. При изменении домена или проксирования обновляйте `server_name` и параметры `fastcgi_pass`.
