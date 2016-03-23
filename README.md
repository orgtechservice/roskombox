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

## Установка

Установку приложения рассмотрим на примере операционной системы Raspbian Jessie (дистрибутив Debian для Raspberry Pi). На «настольной» или «серверной» версии Debian установка производится схожим образом.

Сначала необходимо установить сам Raspbian. Его установка [описана](https://www.raspberrypi.org/downloads/raspbian/) на официальном сайте Raspberry Pi. Затем необходимо установить следующие пакеты:

```bash
apt-get update
apt-get install mysql-server uwsgi uwsgi-emperor uwsgi-plugin-python3 python3-venv build-essential libxml2-dev python3-dev libmysqlclient-dev
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
pip install Django django-bootstrap-pagination django-crispy-forms django-jsonview django-sendmail-backend requests suds-py3 uwsgidecorators
pip install mysqlclient lxml # здесь будет компиляция
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
DEBUG = False
ALLOWED_HOSTS = ['*']
ROSKOM_FROM_EMAIL = 'info@roskombox.local'
DATABASES = {'default': {'ENGINE': 'django.db.backends.mysql', 'NAME': 'roskombox_db', 'USER': 'roskombox', 'PASSWORD': '*********', 'HOST': '', 'PORT': ''}}
```

В качестве пароля, как нетрудно догадаться, указываем тот, который был задан для пользователя MySQL `roskombox@localhost` на этапе создания базы данных.

Пробуем запустить `./manage.py`, пока без аргументов. Если при этом не было сообщений об ошибках, и был выведен список доступных команд, можно перейти к следующему шагу — заполнению БД.

```bash
./manage.py migrate
```

Выполнение этой команды приведёт к последовательному выполнению группы скриптов, называемых миграциями и ответственных за создание таблиц в БД. Соответственно, будет либо успешное выполнение команды, либо сообщение о том, что подключиться к БД не удалось. Если произошла ошибка, необходимо удостовериться, что реквизиты доступа к БД указаны корректно и что СУБД запущена.

