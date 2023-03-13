# empire-cjdr-parsers

PHP version: 7.4

Данный рабочий проект предназначен был, для возможности автоматического сбора информации о поддержанных автомобилях с различных сайтов. Для парсинга используется библиотека [cURL](https://www.php.net/manual/ru/book.curl.php). Данные генерируются в CSV файл и отправляются на Google Drive в определеннную папку с помощью [gdrive](https://github.com/carstentrink/gdrive). В случае возникновения ошибок в работе какого-нибудь из парсеров, письмо об этом отправляется на заранее указанные в конфиге емаилы. Для отправки используется библиотека [PHPMailer](https://github.com/PHPMailer/PHPMailer). Тут не все парсеры представлены.

## Dependency

### gdrive

To upload the parsing result (csv file) to a folder on Google Drive, use the [gdrive](https://github.com/carstentrink/gdrive) Linux command line utility located in the bin/vendor/.

#### Installation of [gdrive](https://github.com/carstentrink/gdrive)

Compile from source code or request precompiled binary.
To compile [gdrive](https://github.com/carstentrink/gdrive) from sourse code:

```sh
git clone --depth=1 https://github.com/carstentrink/gdrive
cd gdrive
```

Edit the 'clientId' and 'clientSecret' in the file handlers_drive.go

```sh
./compile
```

#### Usage gdrive

In order to use gdrive, you need to go through the process of getting a token to work with Google Drive. To do this, you first need to get the authentication URL:

```sh
cd bin/vendor/gdrive/bin
gdrive_linux_amd64 about
```

The authentication process will create a token file in your home folder `~/.config/gdrive`.

#### Example command

Uploading a file to a folder on Google Drive:

```sh
$ bin/vendor/gdrive upload --parent 1BHQEdy3XTZFfW079vq6LPsI8tvfTOoLm parsers/results/empirefordofhuntingtonparser_2022-06-14.csv

where:
1BHQEdy3XTZFfW079vq6LPsI8tvfTOoLm                              Folder id from folder URL on Google Drive
parsers/results/empirefordofhuntingtonparser_2022-06-14.csv    Path to uploading file
```
