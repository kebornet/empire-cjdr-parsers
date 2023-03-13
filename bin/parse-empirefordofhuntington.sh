#!/bin/sh

NOW=$(date +"%Y-%m-%d")

php ../parsers/empirefordofhuntingtonparser.php

vendor/gdrive_linux_amd64 upload --parent 1BHQEdy3XTZFfW079vq6LPsI8tvfTOoLm ../parsers/results/empirefordofhuntingtonparser_$NOW.csv
