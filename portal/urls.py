# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Django
from django.conf.urls import include, url
from portal import views, forms, api
from django.contrib.auth import views as auth_views

urlpatterns = [
	url(r'^$', views.home_page, name = 'home'),
	url(r'^downloads/?$', views.downloads_page, name = 'downloads'),
	url(r'^scans/?$', views.reports_page, name = 'reports'),
	url(r'^settings/?$', views.settings_main_page, name = 'settings'),
	url(r'^settings/auth-files/?$', views.settings_auth_files_page, name = 'settings-auth-files'),
	url(r'^settings/other/?$', views.settings_other_page, name = 'settings-other'),
	url(r'^settings/password/?$', views.settings_password_page, name = 'settings-password'),
	url(r'^settings/ssh/?$', views.settings_ssh_page, name = 'settings-ssh'),
	url(r'^log/?$', views.log_page, name = 'log'),

	# Для открытия в iframe
	url(r'^downloads/(?P<download_id>[\d]+)/?$', views.download_details_page, name = 'download-details'),
	url(r'^scans/(?P<scan_id>[\d]+)/?$', views.scan_details_page, name = 'scan-details'),
	url(r'^scans/(?P<scan_id>[\d]+)/available/?$', views.available_links_page, name = 'available-links'),

	# Пара редиректов
	#url(r'^admin', views.redir_home_page),
	url(r'^accounts/profile/?$', views.redir_home_page),

	# Действия
	url(r'^downloads/new/?$', views.perform_download_page, name = 'perform-download'),
	url(r'^scans/new/?$', views.perform_scan_page, name = 'perform-scan'),

	# Авторизация пользователей
	url(r'^accounts/login/?$', auth_views.login, {'template_name': 'login-page.htt', 'authentication_form': forms.LoginForm}, name = 'login'),
	url(r'^accounts/logout/?$', views.logout_page, name = 'logout'),

	# API
	url(r'^api/add_ssh_key$', api.add_ssh_key, name = 'api.add_ssh_key'),
	url(r'^api/del_ssh_key$', api.del_ssh_key, name = 'api.del_ssh_key'),
	url(r'^api/check_updates$', api.check_updates, name = 'api.check_updates'),
]
