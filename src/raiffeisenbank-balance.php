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

try {
    if (ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'), true) === false) {
        throw new \Exception(sprintf(_('Certificate file %s is not accessible'), Shared::cfg('CERT_FILE')));
    }
    
    // If file is readable, perform deeper certificate validation
    $certFile = Shared::cfg('CERT_FILE');
    $certValidation = null;
    if (is_readable($certFile)) {
        $certValidation = ApiClient::checkCertificate($certFile, Shared::cfg('CERT_PASS'));
    }
} catch (\Exception $certException) {
    $certFile = Shared::cfg('CERT_FILE');
    $certExists = file_exists($certFile);
    $certPerms = $certExists ? decoct(fileperms($certFile) & 0777) : 'N/A';
    $certReadable = $certExists ? is_readable($certFile) : false;
    
    $errorDetails = [
        'problem' => $certException->getMessage(),
        'certificate_file' => $certFile,
        'file_exists' => $certExists,
        'file_permissions' => $certPerms,
        'file_readable' => $certReadable,
    ];
    
    // Add certificate validation result if available
    if (isset($certValidation)) {
        $errorDetails['certificate_validation'] = $certValidation;
    }
    
    $engine->addStatusMessage(sprintf(
        _('Certificate error: %s | File: %s | Exists: %s | Permissions: %s | Readable: %s'),
        $certException->getMessage(),
        $certFile,
        $certExists ? 'yes' : 'no',
        $certPerms,
        $certReadable ? 'yes' : 'no'
    ), 'error');
    
    $report = [
        'status' => 'error',
        'timestamp' => date('c'),
        'message' => $certException->getMessage(),
        'artifacts' => [
            'certificate_file' => $certFile,
        ],
        'metrics' => [
            'certificate_exists' => $certExists,
            'certificate_permissions' => $certPerms,
            'certificate_readable' => $certReadable,
        ],
        'error_details' => $errorDetails,
    ];
    $exitcode = 1;
}

if ($exitcode === 0) {
    $apiInstance = new \VitexSoftware\Raiffeisenbank\PremiumAPI\GetAccountBalanceApi();
    $xRequestId = (string) time();

    try {
        $apiResult = $apiInstance->getBalance($xRequestId, Shared::cfg('ACCOUNT_NUMBER'));

        // Extract balance values for metrics
        $metrics = [
            'account_number' => $apiResult['numberPart2'] ?? '',
            'bank_code' => $apiResult['bankCode'] ?? '',
        ];

        // Add balance metrics for each currency
        foreach ($apiResult['currencyFolders'] ?? [] as $folder) {
            $currency = $folder['currency'] ?? 'UNKNOWN';

            foreach ($folder['balances'] ?? [] as $balance) {
                $balanceType = $balance['balanceType'] ?? 'UNKNOWN';
                $key = strtolower($currency.'_'.$balanceType);
                $metrics[$key] = $balance['value'] ?? 0;
            }
        }

        $report = [
            'status' => 'success',
            'timestamp' => date('c'),
            'message' => _('Balance retrieved successfully'),
            'artifacts' => [
                'balance' => [$destination !== 'php://stdout' ? $destination : 'stdout'],
            ],
            'metrics' => $metrics,
        ];
    } catch (\VitexSoftware\Raiffeisenbank\ApiException $exc) {
        $errorMessage = $exc->getMessage();
        $responseBody = $exc->getResponseBody();

        // Parse API error response if it's JSON
        if ($responseBody && ($decoded = json_decode($responseBody, true))) {
            $errorMessage = $decoded['message'] ?? $errorMessage;
        }

        $report = [
            'status' => 'error',
            'timestamp' => date('c'),
            'message' => $errorMessage,
            'metrics' => [
                'error_code' => $exc->getCode(),
            ],
        ];
        $exitcode = (int) $exc->getCode();

        if (!$exitcode) {
            if (preg_match('/cURL error ([0-9]*):/', $errorMessage, $codeRaw)) {
                $exitcode = (int) $codeRaw[1];
            }
        }
        
        // Ensure non-zero exit code on errors
        if ($exitcode === 0) {
            $exitcode = 1;
        }
    } catch (\InvalidArgumentException $exc) {
        $report = [
            'status' => 'error',
            'timestamp' => date('c'),
            'message' => $exc->getMessage(),
            'metrics' => [
                'error_code' => 4,
            ],
        ];
        $exitcode = 4;
    }
}

$written = file_put_contents($destination, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

// Exit with error code if there was an error, otherwise 0 on success or 2 if write failed
if ($exitcode !== 0) {
    exit($exitcode);
}

exit($written ? 0 : 2);
