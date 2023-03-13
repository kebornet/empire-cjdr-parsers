#!/bin/sh

NOW=$(date +"%Y-%m-%d")

php ../parsers/empirevolvocarssmithtown.php

vendor/gdrive_linux_amd64 upload --parent 1UL-ZvfiXZqfwJbTl-6QrUxUOCUkQOGkz ../parsers/results/empirevolvocarssmithtown_$NOW.csv
