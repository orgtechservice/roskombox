# -*- coding: utf-8 -*-
# Generated by Django 1.9.4 on 2016-03-10 10:42
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('portal', '0005_auto_20160310_1339'),
    ]

    operations = [
        migrations.AddField(
            model_name='download',
            name='debug',
            field=models.BooleanField(default=False, verbose_name='Отладка'),
        ),
    ]
