<?php

/**
 * RaiffeisenBank - Balance.
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.com>
 * @copyright  (C) 2023-2024 Spoje.Net
 */

namespace SpojeNet\RaiffeisenBank;

use Ease\Shared;
use VitexSoftware\Raiffeisenbank\ApiClient;

require_once('../vendor/autoload.php');

const APP_NAME = 'RaiffeisenBankBalance';

/**
 * Get today's transactons list
 */
Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], isset($argv[1]) ? $argv[1] : '../.env');
ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'), true);
$apiInstance = new \VitexSoftware\Raiffeisenbank\PremiumAPI\GetAccountBalanceApi();
$xRequestId = time();
try {
    $result = $apiInstance->getBalance($xRequestId, Shared::cfg('ACCOUNT_NUMBER'));
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo 'Exception when calling GetAccountBalanceApi->getBalance: ', $e->getMessage(), PHP_EOL;
}
