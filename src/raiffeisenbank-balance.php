<?php

declare(strict_types=1);

/**
 * This file is part of the RaiffeisenBank Statement Tools package
 *
 * https://github.com/Spoje-NET/pohoda-raiffeisenbank
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\RaiffeisenBank;

use Ease\Shared;
use VitexSoftware\Raiffeisenbank\ApiClient;

require_once '../vendor/autoload.php';

\define('APP_NAME', 'RaiffeisenBankBalance');

/**
 * Get today's transactions list.
 */
$written = 0;
$exitcode = 0;
$options = getopt('o::e::', ['output::environment::']);
Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], \array_key_exists('environment', $options) ? $options['environment'] : '../.env');
$destination = \array_key_exists('output', $options) ? $options['output'] : Shared::cfg('RESULT_FILE', 'php://stdout');

$engine = new \Ease\Sand();
$engine->setObjectName(Shared::cfg('ACCOUNT_NUMBER'));

if (Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}

if (ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE')) === false) {
    $engine->addStatusMessage(sprintf(_('Certificate file %s problem'), Shared::cfg('CERT_FILE')), 'error');

    $exitcode = 1;
} else {
    $apiInstance = new \VitexSoftware\Raiffeisenbank\PremiumAPI\GetAccountBalanceApi();
    $xRequestId = (string) time();

    try {
            $apiResult = $apiInstance->getBalance($xRequestId, Shared::cfg('ACCOUNT_NUMBER'));
            $report = [
                'status' => 'success',
                'timestamp' => date('c'),
                'message' => _('Balance retrieved successfully'),
                'artifacts' => [
                    'balance' => [json_encode($apiResult)]
                ],
                'metrics' => [
                    'balance' => $apiResult['balance'] ?? null
                ]
            ];
    } catch (\VitexSoftware\Raiffeisenbank\ApiException $exc) {
            $report = [
                'status' => 'error',
                'timestamp' => date('c'),
                'message' => $exc->getMessage(),
                'metrics' => [
                    'error_code' => $exc->getCode()
                ]
            ];
            $exitcode = $exc->getCode();
            if (!$exitcode) {
                if (preg_match('/cURL error ([0-9]*):/', $report['message'], $codeRaw)) {
                    $exitcode = (int) $codeRaw[1];
                }
            }
    } catch (\InvalidArgumentException $exc) {
            $report = [
                'status' => 'error',
                'timestamp' => date('c'),
                'message' => $exc->getMessage(),
                'metrics' => [
                    'error_code' => 4
                ]
            ];
            $exitcode = 4;
    }
}

$written = file_put_contents($destination, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode ?: ($written ? 0 : 2));
