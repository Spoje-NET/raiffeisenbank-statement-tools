<?php

/**
 * RaiffeisenBank - Statements downloader.
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.com>
 * @copyright  (C) 2023-2024 Spoje.Net
 */

namespace SpojeNet\RaiffeisenBank;

use Ease\Shared;
use VitexSoftware\Raiffeisenbank\Statementor;
use VitexSoftware\Raiffeisenbank\ApiClient;

require_once('../vendor/autoload.php');

define('APP_NAME', 'RaiffeisenBank Statement Mailer');

if (array_key_exists(1, $argv) && $argv[1] == '-h') {
    echo 'raiffeisenbank-statement-downloader [email recipient,recipient2,...] [format pdf/xml] [path/to/.env]';
    exit;
}

Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], array_key_exists(3, $argv) ? $argv[3] : '../.env');
ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'));
$engine = new Statementor(Shared::cfg('ACCOUNT_NUMBER'));
if (\Ease\Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}
$engine->setScope(Shared::cfg('STATEMENT_IMPORT_SCOPE', 'yesterday'));
$statements = $engine->getStatements(Shared::cfg('ACCOUNT_CURRENCY', 'CZK'), Shared::cfg('STATEMENT_LINE', 'MAIN'));
if (empty($statements) === false) {
    $downloaded = $engine->download(
            Shared::cfg('STATEMENTS_DIR', getcwd()),
            $statements,
            array_key_exists(2, $argv) ? $argv[2] : Shared::cfg('STATEMENT_FORMAT', 'pdf')
    );

    if ($downloaded) {
        $recipient = array_key_exists(1, $argv) ? $argv[1] : Shared::cfg('SEND_MAIL_TO');
        if (empty($recipient)) {
            fwrite(fopen('php://stderr', 'wb'), \Ease\Shared::appName() . ': ' . _('No recipient provided! Check arguments or environment') . PHP_EOL);
            exit(1);
        } else {
            try {
                $mailer = new \Ease\Mailer($recipient, sprintf(_('Bank Statements %s'), \Ease\Shared::cfg('ACCOUNT_NUMBER')));
                $mailer->addText(sprintf(_('Statements from %s to %s'), $engine->getSince()->format(Statementor::$dateFormat), $engine->getUntil()->format(Statementor::$dateFormat)) . "\n\n");
                foreach ($statements as $stId => $statement) {
                    $mailer->addText(_('Statement') . ' ' . strval($stId + 1) . "\n");
                    $mailer->addText("----------------------------\n");
                    foreach ($statement as $statementKey => $statementValue) {
                        $mailer->addText($statementKey . ': ' . (is_array($statementValue) ? implode(',', $statementValue) : strval($statementValue)) . "\n");
                    }
                    $mailer->addText("\n");
                }
                $mailer->addText("\n" . sprintf(_('Generated by %s %s.'), \Ease\Shared::appName(), \Ease\Shared::AppVersion())."\nhttps://github.com/Spoje-NET/raiffeisenbank-statement-downloader");
                foreach ($downloaded as $statement) {
                    $mailer->addFile($statement, mime_content_type($statement));
                    if (file_exists($statement)) {
                        unlink($statement);
                    }
                }
                $mailer->send();
            } catch (Exception $exc) {
                
            }
        }
    }
} else {
    $engine->addStatusMessage('no statements returned');
}
