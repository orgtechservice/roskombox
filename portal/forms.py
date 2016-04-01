# -*- coding: utf-8 -*-

#------------------------------------------------------------------------------------#
# Этот файл является частью приложения Roskombox, разработанного ООО «Оргтехсервис». #
# https://github.com/orgtechservice/roskombox                                        #
# Предоставляется на условиях GNU GPL v3                                             #
#------------------------------------------------------------------------------------#

import django.forms
from django.contrib.auth.forms import *
from django.core.urlresolvers import reverse
from portal.models import *

# хрустящие формы
from crispy_forms.helper import FormHelper
from crispy_forms.layout import *

class LoginForm(AuthenticationForm):
	def __init__(self, *args, **kwargs):
		super(LoginForm, self).__init__(*args, **kwargs)
		self.helper = FormHelper()
		self.helper.layout = Layout (
			Fieldset (
				'',
				Field('username', required = 'required', css_class = 'username-field', placeholder = 'Имя пользователя'),
				Field('password', required = 'required', css_class = 'password-field', placeholder = 'Пароль'),
				css_class = 'login-fields'
			),
			Div (
				Submit('submit', u'Войти в систему', css_class = 'btn btn-danger'),
				css_class = 'form-actions'
			)
		)

class AuthFilesForm(forms.Form):
	request_xml = forms.FileField(u'request.xml', label = 'Файл запроса (request.xml)', help_text = 'Файл запроса, полученный от Роскомнадзора')
	request_xml_sign = forms.FileField(u'request.xml.sign', label = 'Файл подписи (request.xml.sign)', help_text = 'Электронная подпись к запросу, полученная от Роскомнадзора')

	def __init__(self, *args, **kwargs):
		super(AuthFilesForm, self).__init__(*args, **kwargs)
		self.helper = FormHelper()
		self.helper.layout = Layout (
			Fieldset (
				'',
				Field('request_xml'),
				Field('request_xml_sign'),
			),
			Div (
				Submit('submit', u'Сохранить', css_class = 'btn btn-danger'),
				css_class = 'form-actions'
			)
		)

class OtherSettingsForm(forms.Form):
	email = forms.EmailField(label = u'E-mail для уведомлений', help_text = u'Адрес электронной почты, на который будут отправляться уведомления', required = False)
	max_available = forms.IntegerField(label = u'Максимальное число доступных сайтов', help_text = u'При превышении этого числа будет формироваться предупреждение. 0 отключает контроль.', min_value = 0)
	search_substring = forms.CharField(label = u'Подстрока для поиска', help_text = u'При вхождении этой подстроки сайт будет считаться заблокированным. По умолчанию «eais.rkn.gov.ru».', required = False)

	def __init__(self, *args, **kwargs):
		super(OtherSettingsForm, self).__init__(*args, **kwargs)
		self.helper = FormHelper()
		self.helper.layout = Layout (
			Fieldset (
				'',
				Field('email'),
				Field('max_available'),
				Field('search_substring'),
			),
			Div (
				Submit('submit', u'Сохранить', css_class = 'btn btn-danger'),
				css_class = 'form-actions'
			)
		)

class PasswordSettingsForm(forms.Form):
	password = forms.CharField(label = u'Текущий пароль', help_text = u'Укажите текущий пароль, если хотите изменить его', widget = forms.PasswordInput)
	new_password_1 = forms.CharField(label = u'Новый пароль', help_text = u'Задайте новый пароль', widget = forms.PasswordInput)
	new_password_2 = forms.CharField(label = u'Подтверждение', help_text = u'Введите новый пароль повторно во избежание ошибок', widget = forms.PasswordInput)

	def clean_password(self):
		password = self.cleaned_data.get('password', None)
		if not self.user.check_password(password):
			raise ValidationError('Неверный пароль')

	def clean_new_password_2(self):
		password1 = self.cleaned_data.get('new_password_1', None)
		password2 = self.cleaned_data.get('new_password_2', None)
		if password1 != password2:
			raise ValidationError('Несоответствие')

	def __init__(self, user, data, *args, **kwargs):
		self.user = user
		super(PasswordSettingsForm, self).__init__(data = data, *args, **kwargs)
		self.helper = FormHelper()
		self.helper.layout = Layout (
			Fieldset (
				'',
				Field('password'),
				Field('new_password_1'),
				Field('new_password_2'),
			),
			Div (
				Submit('submit', u'Сохранить', css_class = 'btn btn-danger'),
				css_class = 'form-actions'
			)
		)

class SshSettingsForm(forms.Form):
	key = forms.CharField(label = u'Содержимое ключа', help_text = u'Вставьте содержимое вашего публичного ключа в это поле', widget = forms.Textarea)
	def __init__(self, *args, **kwargs):
		super(SshSettingsForm, self).__init__(*args, **kwargs)
		self.form_tag = False

		self.helper = FormHelper()
		self.helper.layout = Layout (
			Fieldset (
				'',
				Field('key', css_class = 'ssh_key_area'),
			)
		)

class AutoSettingsForm(forms.Form):
	pass
