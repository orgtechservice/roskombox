
############################ AUTOCONF VARS ###################################

# архитектура для которой будет собираться deb-пакет.
# сборщик пакетов и компилятор имеют разную терминлогию,
# в скрипте configure мы транслируем HOST_CPU в имя архитектуры которую
# ожидает менеджер пакетов
DEB_ARCH:=all

############################### VARS #########################################

# Имя файла deb-пакета
DEB_FILENAME=roskombox-fakezonegen-$(DEB_ARCH).deb

############################# GENERIC RULES ##################################

# .PHONY указывает цели которые не создают файлов.
.PHONY: all deb clean distclean

all: deb

# сборка deb-пакетов, просто короткая цель чтобы не вспоминать имя пакета
deb: deb_clean $(DEB_FILENAME)
	echo $(DEB_FILENAME)

# сборка пакета
$(DEB_FILENAME): deb_clean deb_install
	fakeroot dpkg-deb --build deb $(DEB_FILENAME)

.PHONY: deb_install
deb_install: $(PROG_FILENAME)
	mkdir -p deb/etc/maycloud
	mkdir -p deb/var/lib/maycloud/php-exec

	install -m 0755 fakezonegen.php deb/var/lib/maycloud/php-exec
	install -m 0644 config.php.example deb/etc/maycloud/fakezonegen.php

########################### CLEAN RULES ######################################

# полная очистка
distclean: deb_clean
	rm -rf $(DEB_FILENAME)

# простая очистка, промежуточные файлы, но оставляет целевые
clean: deb_clean

# зачистка в каталоге deb-пакета
.PHONY: deb_clean
deb_clean:
	rm -rf deb/etc
	rm -rf deb/var
