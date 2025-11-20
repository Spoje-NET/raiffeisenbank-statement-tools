Raiffeisenbank Statement Tools
==============================

A set of tools for downloading and subsequent operations with Raiffaissenbank bank statements

[![View raiffeisenbank-statement-downloader on GitHub](https://img.shields.io/github/stars/Spoje-NET/raiffeisenbank-statement-downloader?color=232323&label=raiffeisenbank-statement-downloader&logo=github&labelColor=232323)](https://github.com/Spoje-NET/raiffeisenbank-statement-downloader) 
[![Author Spoje-NET](https://img.shields.io/badge/Spoje-NET-b820f9?labelColor=b820f9&logo=githubsponsors&logoColor=fff)](https://github.com/Spoje-NET) ![Written in PHP](https://img.shields.io/static/v1?label=&message=PHP&color=777BB4&logo=php&logoColor=FFFFFF)


Statement Downloader
--------------------

![raiffeisenbank-statement-downloader](raiffeisenbank-statement-downloader.svg?raw=true)

Download bank statements for the required period in the required format to the specified or current folder

```shell
raiffeisenbank-statement-downloader [save/to/directory] [format] [path/to/.env]
```

Example output when EASE_LOGGER=console

```
12/01/2023 16:37:10 ⚙ ❲RaiffeisenBank Statement Downloader⦒123456789@VitexSoftware\Raiffeisenbank\Statementor❳ Request statements from 2023-11-30 to 2023-11-30
12/01/2023 16:37:13 🌼 ❲RaiffeisenBank Statement Downloader⦒123@VitexSoftware\Raiffeisenbank\Statementor❳ 10_2023_123_3780381_CZK_2023-11-01.xml saved
12/01/2023 16:37:13 ℹ ❲RaiffeisenBank Statement Downloader⦒123456789@VitexSoftware\Raiffeisenbank\Statementor❳ Download done. 1 of 1 saved

```

Statement mailer
----------------

![raiffeisenbank-statement-downloader](raiffeisenbank-statement-mailer.svg?raw=true)

Download bank statements for the required period in the required format and send it by email

```shell
raiffeisenbank-statement-mailer <recipient@domain,[recipient1@domain,...]> [format] [path/to/.env]
```

Balance Check
-------------

![raiffeisenbank-balance](raiffeisenbank-balance.svg?raw=true)

```shell
raiffeisenbank-balance --output="/tmp/balance.json" [--environment="path/to/.env"]
```

Options:
- `--output` or `-o`: Path to output file (default: stdout or RESULT_FILE from config)
- `--environment` or `-e`: Path to .env configuration file

Example output:

```json
{
    "numberPart2": "635814116",
    "bankCode": "5500",
    "currencyFolders": [
        {
            "currency": "CZK",
            "status": "ACTIVE",
            "balances": [
                {
                    "balanceType": "CLAB",
                    "currency": "CZK",
                    "value": 5883.89
                },
                {
                    "balanceType": "CLBD",
                    "currency": "CZK",
                    "value": 5883.89
                },
                {
                    "balanceType": "CLAV",
                    "currency": "CZK",
                    "value": 20853.89
                },
                {
                    "balanceType": "BLCK",
                    "currency": "CZK",
                    "value": 0
                }
            ]
        },
        {
            "currency": "EUR",
            "status": "ACTIVE",
            "balances": [
                {
                    "balanceType": "CLAB",
                    "currency": "EUR",
                    "value": 133.76
                },
                {
                    "balanceType": "CLBD",
                    "currency": "EUR",
                    "value": 133.76
                },
                {
                    "balanceType": "CLAV",
                    "currency": "EUR",
                    "value": 133.76
                },
                {
                    "balanceType": "BLCK",
                    "currency": "EUR",
                    "value": 0
                }
            ]
        }
    ]
}
```

Transaction Report
------------------

![raiffeisenbank-transaction-report](raiffeisenbank-transaction-report.svg?raw=true)

```shell
raiffeisenbank-transaction-report --output="/tmp/transaction_report.json" [--environment="path/to/.env"]
```

Options:
- `--output` or `-o`: Path to output file (default: stdout or RESULT_FILE from config)
- `--environment` or `-e`: Path to .env configuration file

> **Note:** The chosen `STATEMENT_LINE` must support statement frequency for `REPORT_SCOPE`

### Report Format

All tools now output schema-compliant JSON reports with the following structure:

```json
{
    "status": "success",
    "timestamp": "2024-03-31T12:00:00+00:00",
    "message": "Operation completed successfully",
    "artifacts": {
        "transactions": []
    },
    "metrics": {
        "in_total": 34,
        "out_total": 34,
        "in_sum_total": 313035.22,
        "out_sum_total": 347705.63
    }
}
```

Example output (legacy format):

```json
{
    "source": "000000000@VitexSoftware\\Raiffeisenbank\\Statementor",
    "account": "000000000",
    "in": {
        "2024-03-06T11:17:34": "2904",
        "2024-03-07T06:02:04": "19602",
        "2024-03-12T11:29:04": "363",
        "2024-03-12T12:55:05": "363",
        "2024-03-15T10:29:36": "26892.25",
        "2024-03-19T01:11:40": "1815",
        "2024-03-19T05:25:52": "726",
        "2024-03-19T15:05:42": "3630",
        "2024-03-21T17:56:34": "77621.5",
        "2024-03-22T06:02:01": "1996.5",
        "2024-03-25T10:26:12": "2359.5",
        "2024-03-25T12:52:38": "5989.5",
        "2024-03-27T17:59:30": "3085.5",
        "2024-02-02T11:26:26": "12342",
        "2024-02-06T14:05:14": "363",
        "2024-02-06T14:06:09": "181.5",
        "2024-02-12T13:45:10": "1452",
        "2024-02-13T01:16:16": "275.88",
        "2024-02-13T08:13:16": "181.5",
        "2024-02-14T11:55:21": "968",
        "2024-02-14T15:51:04": "1694",
        "2024-02-14T16:35:02": "10527",
        "2024-02-15T01:25:24": "2178",
        "2024-02-15T19:26:15": "1058.75",
        "2024-02-15T19:26:40": "1270.5",
        "2024-02-17T18:59:54": "726",
        "2024-02-20T11:52:17": "907.5",
        "2024-02-22T11:54:16": "2359.5",
        "2024-02-25T20:27:46": "12069.75",
        "2024-02-27T17:18:38": "7018",
        "2024-02-29T01:32:39": "7199.5"
    },
    "out": {
        "2024-03-01T15:09:43": "12000",
        "2024-03-01T15:10:12": "16000",
        "2024-03-08T11:52:50": "46222",
        "2024-03-13T06:06:37": "2928",
        "2024-03-15T06:16:36": "632",
        "2024-03-15T13:37:10": "20000",
        "2024-03-16T06:17:31": "399",
        "2024-03-17T06:22:15": "2654",
        "2024-03-20T06:08:20": "2552",
        "2024-03-20T15:25:44": "1367",
        "2024-03-25T16:56:26": "21222",
        "2024-03-26T20:42:37": "2520.13",
        "2024-03-26T20:55:40": "181.5",
        "2024-03-26T20:57:12": "16692",
        "2024-03-26T20:58:46": "10000",
        "2024-03-26T21:00:27": "3375",
        "2024-03-26T21:01:29": "6380",
        "2024-03-26T21:02:10": "3038",
        "2024-03-26T21:03:51": "23037",
        "2024-03-26T21:04:44": "4200",
        "2024-03-26T21:05:44": "17700",
        "2024-03-26T21:06:34": "800",
        "2024-03-26T21:10:06": "16692",
        "2024-03-26T21:11:02": "10000",
        "2024-03-26T21:11:50": "3375",
        "2024-03-26T21:12:34": "6380",
        "2024-03-26T21:13:12": "3038",
        "2024-03-31T23:59:59": "63.55",
        "2024-02-02T18:06:22": "20000",
        "2024-02-10T01:09:24": "24100",
        "2024-02-21T11:24:38": "50000",
        "2024-02-29T23:59:59": "59.45"
    },
    "in_total": 34,
    "out_total": 34,
    "in_sum_total": 313035.22000000003,
    "out_sum_total": 347705.63,
    "from": "2024-02-01",
    "to": "2024-03-31",
    "iban": "CZ0000000000000000000000"
}
```

Exit Codes
----------

0: success - report sent
1: certificate file problem
2: error saving report
4xx: Permission Denied
5xx: Server error

Configuration
-------------

Please set this environment variables or specify path to .env file

```env
CERT_FILE='RAIFF_CERT.p12'
CERT_PASS=CertPass
XIBMCLIENTID=PwX4XXXXXXXXXXv6I
ACCOUNT_NUMBER=666666666
ACCOUNT_CURRENCY=CZK
STATEMENT_FORMAT=pdf | xml | MT940
STATEMENT_LINE=MAIN
STATEMENT_IMPORT_SCOPE=last_two_months
STATEMENTS_DIR=~/Documents/
API_DEBUG=True
APP_DEBUG=True
EASE_LOGGER=syslog|eventlog|console

# Report file configuration (optional, applies to all tools):
REPORT_FILE=/path/to/report.json
RESULT_FILE=/path/to/result.json

# Mailer specific:
STATEMENTS_TO=statement@recipient.com
STATEMENTS_FROM=email@address.com
STATEMENTS_REPLYTO=email@address.com
STATEMENTS_CC=email@address.com
```


Availble Import Scope Values

----------------------------

* 'yesterday'
* 'current_month'
* 'last_month'
* 'last_two_months'
* 'previous_month'
* 'two_months_ago'
* 'this_year'
* 'January'
* 'February'
* 'March'
* 'April'
* 'May'
* 'June'
* 'July'
* 'August'
* 'September'
* 'October'
* 'November',
* 'December'

Created using the library [php-rbczpremiumapi](https://github.com/VitexSoftware/php-vitexsoftware-rbczpremiumapi)


MultiFlexi
----------

**Raiffeisenbank Statement Downloader** is ready for run as [MultiFlexi](https://multiflexi.eu) application.
See the full list of ready-to-run applications within the MultiFlexi platform on the [application list page](https://www.multiflexi.eu/apps.php).

[![MultiFlexi App](https://github.com/VitexSoftware/MultiFlexi/blob/main/doc/multiflexi-app.svg)](https://www.multiflexi.eu/apps.php)

Development
-----------

### Requirements

- PHP 8.4 or later
- Composer

### Setup

```bash
# Install dependencies
composer install
```

### Testing

```bash
# Run tests
composer test

# Lint code
composer lint

# Fix code style issues
composer fix
```

### Code Quality

The project follows PSR-12 coding standards and includes:
- PHPUnit for testing
- PHP CS Fixer for code formatting
- PHPStan for static analysis

All scripts must pass `php -l` syntax check before committing.

Installation
------------

The repository with packages for Debian & Ubuntu is available:

```shell
sudo apt install lsb-release wget apt-transport-https bzip2
wget -qO- https://repo.vitexsoftware.com/keyring.gpg | sudo tee /etc/apt/trusted.gpg.d/vitexsoftware.gpg
echo "deb [signed-by=/etc/apt/trusted.gpg.d/vitexsoftware.gpg]  https://repo.vitexsoftware.com  $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo apt update
sudo apt install raiffeisenbank-statement-tools
```

See Also
--------

* [fiobank-statement-tools](https://github.com/Spoje-NET/fiobank-statement-tools)
* [csas-statement-tools](https://github.com/VitexSoftware/csas-statement-tools)

## Exit Codes

Applications in this package use the following exit codes:

- `0`: Success
- `1`: General error
- `4`: Application-specific error
