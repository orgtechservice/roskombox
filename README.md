# Роскомбокс

Роскомбокс — веб-приложение для проведения ручных и автоматических выгрузок реестра Роскомнадзора и проверок доступности сайтов из реестра.

## Описание

Роскомбокс разработан с использованием языка Python версии 3.х и фреймворка Django версии 1.9.4. Также имеется ряд других зависимостей:

* suds — для взаимодействия по протоколу SOAP
* lxml — для работы с XML-файлами
* django-crispy-forms — для генерации форм
* django-bootstrap-pagination — для постраничного вывода
* django-jsonview — для работы JSON API
* django-sendmail-backend — для отправки почты через локальный MTA
* mysqlclient — для работы с СУБД MySQL
* requests — для тестовых обращений к сайтам из реестра
* uwsgidecorators — для удобного взаимодействия с uWSGI API

В качестве app-сервера используется uWSGI (другие app-серверы не поддерживаются, так как используются некоторые специфические возможности uWSGI); для frontend-сервера рекомендуется использовать nginx.

## Установка (для разработчиков)

Установку приложения рассмотрим на примере операционной системы Raspbian Jessie (дистрибутив Debian для Raspberry Pi). На «настольной» или «серверной» версии Debian установка производится схожим образом.

Сначала необходимо установить сам Raspbian. Его установка [описана](https://www.raspberrypi.org/downloads/raspbian/) на официальном сайте Raspberry Pi. Затем необходимо установить следующие пакеты:

```bash
apt-get update
apt-get install mysql-server uwsgi uwsgi-emperor uwsgi-plugin-python3 python3-venv build-essential libxml2-dev python3-dev libmysqlclient-dev libxslt1-dev nginx-full sqlite3 librrd-dev
```

Dev-пакеты и средства разработки необходимы в силу того, что при установке через pip пакетов с PyPI будет произведена компиляция исходных кодов на C.

Все три сервера (MySQL, uWSGI и nginx) будут автоматически добавлены в автозагрузку. Однако, нам необходимо их настроить. Начнём с настройки MySQL. Она будет заключаться в создании базы данных для нашего приложения.

```bash
mysql -u root -p
```

Введя пароль администратора, попадаем в командную строку MySQL-клиента, где необходимо создать базу данных:

```sql
CREATE DATABASE roskombox_db CHARACTER SET UTF8;
GRANT ALL ON roskombox_db.* TO roskombox@localhost IDENTIFIED BY '*********';
FLUSH PRIVILEGES;
```

Пароль, заданный пользователю `roskombox@localhost`, необходимо сохранить, так как он понадобится позднее.

Добавим пользователя и склонируем репозиторий Roskombox, попутно создав виртуальное окружение, в которое будут установлены зависимости Roskombox. **Важно**: lxml имеет волшебное свойство компилироваться не просто долго, а очень долго; на медленной машине, каковой является Raspberry Pi, это запросто может занять минут 20-30.

```bash
useradd -m -s /bin/bash admin
su - admin
mkdir ~/venvs
pyvenv ~/venvs/roskombox
source ~/venvs/roskombox/bin/activate
pip install Django==1.9.9 django-bootstrap-pagination django-crispy-forms django-jsonview django-sendmail-backend requests suds-py3 uwsgidecorators
pip install rrdtool mysqlclient lxml # здесь будет компиляция
mkdir ~/www
cd ~/www
git clone https://github.com/orgtechservice/roskombox.git
cd roskombox
```

Теперь необходимо произвести начальную настройку приложения. Для этого создадим файл `local_settings.py`, скопировав готовый образец.

```bash
cp roskombox/local_settings.example.py roskombox/local_settings.py
```

Откроем этот файл для редактирования и заполним следующим образом:

```python
DEBUG = True
ALLOWED_HOSTS = ['*']
ROSKOM_FROM_EMAIL = 'info@roskombox.local'
DATABASES = {'default': {'ENGINE': 'django.db.backends.mysql', 'NAME': 'roskombox_db', 'USER': 'roskombox', 'PASSWORD': '*********', 'HOST': '', 'PORT': ''}}
```

В качестве пароля, как нетрудно догадаться, указываем тот, который был задан для пользователя MySQL `roskombox@localhost` на этапе создания базы данных.

Пробуем запустить `./manage.py`, пока без аргументов. Если при этом не было сообщений об ошибках, и был выведен список доступных команд, можно перейти к следующему шагу — заполнению БД. Для этого выполним команду `./manage.py migrate`. Выполнение этой команды приведёт к последовательному выполнению группы скриптов, называемых миграциями и ответственными за создание таблиц в БД. Соответственно, будет либо успешное выполнение команды, либо сообщение о том, что подключиться к БД не удалось. Если произошла ошибка, необходимо удостовериться, что реквизиты доступа к БД указаны корректно и что СУБД запущена.

После того, как команда `./manage.py migrate` отработала успешно, можно попробовать запустить приложение для теста в режиме отладки при помощи команды `./manage.py runserver 0.0.0.0:8000`, затем при помощи браузера открыть IP-адрес машины, на которую производится установка, с добавлением порта 8000, например: `http://192.168.1.5:8000/`. Если при этом отобразилась страница входа, можно продолжить настройку.

Далее вновь открываем для редактирования файл `roskombox/local_settings.py` и меняем значение переменной `DEBUG` с `True` на `False`. Затем запускаем `./manage.py collectstatic` — это приведёт к копированию статических файлов в каталог, откуда они будут раздаваться при помощи nginx. Наконец, запускаем `./manage.py createsuperuser` и отвечаем на вопросы мастера создания пользователя системы (это можно сделать и позднее).

Теперь отредактируем `/etc/nginx/sites-available/default` следующим образом:

```nginx
server {
        listen 80 default_server;
        server_name roskombox.local;
        charset utf-8;
        client_max_body_size 128m;

        location /static {
                root /home/admin/www/roskombox;
                expires 1y;
        }

        location /media {
                root /home/admin/www/roskombox;
                expires 1y;
        }

        location / {
                include uwsgi_params;
                #uwsgi_intercept_errors on;
                uwsgi_pass 127.0.0.1:8807;
        }

        #include errors.conf;
}
```

Проверим правильность синтаксиса конфигурационного файла при помощи команды `nginx -t` и, если всё нормально, заставим nginx перезагрузить конфигурацию командой `systemctl reload nginx` или `killall -HUP nginx`. Если на машине нет других сайтов, можно и перезапустить nginx командой `systemctl restart nginx`.

Теперь настроим uWSGI. Его настройка будет состоять из 2 шагов: настройка императора и настройка вассала. Не заостряя внимание на сути этих терминов, внесём следующие правки: сначала отредактируем файл `/etc/uwsgi-emperor/emperor.ini`. Помещаем туда следующее (остальное удаляем).

```ini
[uwsgi]
emperor = /etc/uwsgi-emperor/vassals
#vassals-inherit = common/defaults.ini
no-orphans = true
```

**Важно**: на старых версиях uWSGI (версию можно посмотреть при помощи команды `uwsgi --version`) указанная конфигурация в силу присутствующего в этих версиях бага может приводить к проблемам, связанным с переменными окружения (они не устанавливаются корректно при смене пользователя). В качестве workaround на версиях, более старых, чем `2.0.7-debian`, рекомендуется указать в `emperor.ini` директиву `uid = admin` (в нормальном состоянии император работает от имени суперпользователя).

Создаём файл `/etc/uwsgi-emperor/vassals/roskombox.ini` с таким содержимым:

```ini

[uwsgi]
socket = 127.0.0.1:8807
processes = 10
cheaper = 2
master = true
max-requests = 1000
threads = 4
chdir = /home/admin/www/roskombox
env = DJANGO_SETTINGS_MODULE=roskombox.settings
virtualenv = /home/admin/venvs/roskombox
module = roskombox.wsgi:application
plugins = python3
#static-map = /static=/home/admin/www/roskombox/portal/static
mule = 1
auto-procname = true
procname-prefix-spaced = [roskombox]
uid = admin
```

**Важно**: на старых версиях uWSGI (версию можно посмотреть при помощи команды `uwsgi --version`) указанная конфигурация в силу присутствующего в этих версиях бага может вызывать dead lock воркеров. Для версий, более старых, чем `2.0.7-debian`, рекомендуется убрать директиву `max-requests`.

Редактируем `/etc/uwsgi-emperor/emperor.ini` (то, что там есть — удаляем):

```ini
[uwsgi]
no-orphans = true
emperor = /etc/uwsgi-emperor/vassals
```

Перезапускаем стек uWSGI: `systemctl restart uwsgi`, смотрим содержимое журнала: `tail -f /var/log/uwsgi/emperor.log` (или `journalctl -lf` на некоторых ОС). Если сообщений об ошибках нет, значит, развёртывание приложения успешно завершено. Можно пользоваться им, обращаясь из браузера по IP-адресу или добавив в локальном DNS-сервере зону `roskombox.local` с A-записью, указывающей на IP устройства с развёрнутым приложением.

## Установка (для пользователей)

