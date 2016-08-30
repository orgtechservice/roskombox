# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

# Внимание! Это глобальный urls.py проекта. Добавлять свои URL-ки лучше в urls.py приложения «portal».

# Django
from django.conf.urls import include, url
from django.conf import settings
from django.views.static import serve as serve_static

# Наш проект
from portal import urls as portal_urls

urlpatterns = [
	url(r'', include(portal_urls)),

	# Чисто отладочная строка
	url(r'^media/(?P<path>.*)$', serve_static, {'document_root': settings.MEDIA_ROOT}),
]
