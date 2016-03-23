#!/bin/bash

find . -type f -name '*.pyc' -delete
find . -name '__pycache__' -type d -delete
echo "done"
