
############################ AUTOCONF VARS ###################################

# архитектура для которой будет собираться deb-пакет.
# сборщик пакетов и компилятор имеют разную терминлогию,
# в скрипте configure мы транслируем HOST_CPU в имя архитектуры которую
# ожидает менеджер пакетов
DEB_ARCH:=all

############################### VARS #########################################

# Имя файла deb-пакета
DEB_FILENAME=roskombox-slave-$(DEB_ARCH).deb

############################# GENERIC RULES ##################################

# .PHONY указывает цели которые не создают файлов.
.PHONY: all deb clean distclean

all: deb

# сборка deb-пакетов, просто короткая цель чтобы не вспоминать имя пакета
deb: deb_clean $(DEB_FILENAME)
	echo $(DEB_FILENAME)

# сборка пакета
$(DEB_FILENAME): deb_clean deb_install
	fakeroot dpkg-deb --build output/roskombox-slave $(DEB_FILENAME)

.PHONY: deb_install
deb_install: $(PROG_FILENAME)
	mkdir -p output/roskombox-slave/opt/roskomcheck
	mkdir -p output/roskombox-slave/usr/bin
	cp -r extra/DEBIAN output/roskombox-slave
	cp config.py.example output/roskombox-slave/opt/roskomcheck/config.py
	install -m 0755 roskomcheck.py output/roskombox-slave/opt/roskomcheck/roskomcheck.py
	install -m 0755 checkurl.py output/roskombox-slave/opt/roskomcheck/checkurl.py
	install -m 0755 extra/roskomcheck output/roskombox-slave/usr/bin/roskomcheck
	install -m 0755 extra/checkurl output/roskombox-slave/usr/bin/checkurl

########################### CLEAN RULES ######################################

# полная очистка
distclean: deb_clean
	rm -rf $(DEB_FILENAME)

# простая очистка, промежуточные файлы, но оставляет целевые
clean: deb_clean

# зачистка в каталоге deb-пакета
.PHONY: deb_clean
deb_clean:
	rm -rf output
