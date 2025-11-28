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
use VitexSoftware\Raiffeisenbank\Statementor;

require_once '../vendor/autoload.php';

\define('APP_NAME', 'RaiffeisenBank Statement Reporter');

$options = getopt('o::e::', ['output::environment::']);
Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], \array_key_exists('environment', $options) ? $options['environment'] : '../.env');
$destination = \array_key_exists('output', $options) ? $options['output'] : Shared::cfg('RESULT_FILE', 'php://stdout');

$engine = new Statementor(Shared::cfg('ACCOUNT_NUMBER'));

if (Shared::cfg('STATEMENT_LINE')) {
    $engine->setStatementLine(Shared::cfg('STATEMENT_LINE'));
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
    $certPerms = $certExists ? decoct(fileperms($certFile) & 0o777) : 'N/A';
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
        $certReadable ? 'yes' : 'no',
    ), 'error');

    // Schema-compliant error report
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

    $written = file_put_contents($destination, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
    $engine->addStatusMessage(sprintf(_('Saving error report to %s'), $destination), $written ? 'success' : 'error');

    exit(1);
}

$engine->setScope(Shared::cfg('REPORT_SCOPE', 'yesterday'));

if (Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner();
}

$exitcode = 0;

try {
    $status = 'ok';
    $statements = $engine->getStatements(Shared::cfg('ACCOUNT_CURRENCY', 'CZK'), Shared::cfg('STATEMENT_LINE', 'ADDITIONAL'));
} catch (\VitexSoftware\Raiffeisenbank\ApiException $exc) {
    $status = $exc->getMessage();
    $exitcode = (int) $exc->getCode();

    // Try to extract HTTP status code from error message if getCode() returns 0
    if ($exitcode === 0 && preg_match('/\[(\d{3})\]/', $status, $matches)) {
        $exitcode = (int) $matches[1];
    }

    if ($exitcode === 0) {
        $exitcode = 1; // Ensure non-zero exit code on errors
    }

    $statements = [];
}

$payments = [
    'source' => \Ease\Logger\Message::getCallerName($engine),
    'account' => Shared::cfg('ACCOUNT_NUMBER'),
    'status' => $status,
    'in' => [],
    'out' => [],
    'in_total' => 0,
    'out_total' => 0,
    'in_sum_total' => 0,
    'out_sum_total' => 0,
    'from' => $engine->getSince()->format('Y-m-d'),
    'to' => $engine->getUntil()->format('Y-m-d'),
];

if (empty($statements) === false) {
    $payments['status'] = 'statement '.$statements[0]['statementId'];

    try {
        foreach ($engine->download(sys_get_temp_dir(), $statements, 'xml') as $statement => $xmlFile) {
            // ISO 20022 XML to transaction array
            $statementArray = json_decode(json_encode(simplexml_load_file($xmlFile)), true);

            $payments['iban'] = $statementArray['BkToCstmrStmt']['Stmt']['Acct']['Id']['IBAN'];

            $entries = (\array_key_exists('Ntry', $statementArray['BkToCstmrStmt']['Stmt']) ? (array_keys($statementArray['BkToCstmrStmt']['Stmt']['Ntry'])[0] === 0 ? $statementArray['BkToCstmrStmt']['Stmt']['Ntry'] : [$statementArray['BkToCstmrStmt']['Stmt']['Ntry']]) : []);

            foreach ($entries as $payment) {
                $payments[$payment['CdtDbtInd'] === 'CRDT' ? 'in' : 'out'][$payment['BookgDt']['DtTm']] = $payment['Amt'];
                $payments[$payment['CdtDbtInd'] === 'CRDT' ? 'in_sum_total' : 'out_sum_total'] += (float) $payment['Amt'];
                ++$payments[$payment['CdtDbtInd'] === 'CRDT' ? 'in_total' : 'out_total'];
            }

            unlink($xmlFile);
        }
    } catch (\VitexSoftware\Raiffeisenbank\ApiException $exc) {
        $exitcode = (int) $exc->getCode();

        // Try to extract HTTP status code from error message if getCode() returns 0
        if ($exitcode === 0 && preg_match('/\[(\d{3})\]/', $exc->getMessage(), $matches)) {
            $exitcode = (int) $matches[1];
        }

        if ($exitcode === 0) {
            $exitcode = 1; // Ensure non-zero exit code on errors
        }

        $payments['status'] = $exc->getMessage();
    }
} else {
    if ($exitcode === 0) {
        $payments['status'] = 'no statements returned';
    } else {
        $payments['status'] = $status;
    }
}

// Schema-compliant report
$report = [
    'status' => ($exitcode === 0) ? 'success' : 'error',
    'timestamp' => date('c'),
    'message' => $payments['status'] ?? ($exitcode === 0 ? _('Report generated successfully') : _('Error occurred during report generation')),
    'artifacts' => [
        'report' => [$destination !== 'php://stdout' ? $destination : 'stdout'],
    ],
    'metrics' => [
        'in_total' => $payments['in_total'] ?? 0,
        'out_total' => $payments['out_total'] ?? 0,
        'in_sum_total' => $payments['in_sum_total'] ?? 0,
        'out_sum_total' => $payments['out_sum_total'] ?? 0,
        'account' => $payments['account'] ?? '',
        'iban' => $payments['iban'] ?? '',
        'from' => $payments['from'] ?? '',
        'to' => $payments['to'] ?? '',
    ],
];

$written = file_put_contents($destination, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

// Exit with error code if there was an error, otherwise 0 on success or 2 if write failed
if ($exitcode !== 0) {
    exit($exitcode);
}

exit($written ? 0 : 2);
