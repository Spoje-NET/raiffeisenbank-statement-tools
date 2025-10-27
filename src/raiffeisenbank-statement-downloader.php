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

\define('APP_NAME', 'RaiffeisenBank Statement Downloader');

if (\array_key_exists(1, $argv) && $argv[1] === '-h') {
    echo 'raiffeisenbank-statement-downloader [save/to/directory] [format] [path/to/.env]';
    echo "\n";

    exit;
}

Shared::init(['CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID', 'ACCOUNT_NUMBER'], \array_key_exists(3, $argv) ? $argv[3] : '../.env');
$engine = new Statementor(Shared::cfg('ACCOUNT_NUMBER'));

if (\Ease\Shared::cfg('APP_DEBUG', false)) {
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
    
    $reportFile = Shared::cfg('REPORT_FILE', 'statement_report.json');
    $written = file_put_contents($reportFile, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
    $engine->addStatusMessage(sprintf(_('Saving error report to %s'), $reportFile), $written ? 'success' : 'error');
    
    exit(1);
}

$engine->setScope(Shared::cfg('STATEMENT_SCOPE', 'last_month'));

$exitcode = 0;
try {
    $status = 'ok';
    $statements = $engine->getStatements(Shared::cfg('ACCOUNT_CURRENCY', 'CZK'), Shared::cfg('STATEMENT_LINE', 'MAIN'));
} catch (\VitexSoftware\Raiffeisenbank\ApiException $exc) {
    $status = $exc->getCode().': error';
    $exitcode = (int) $exc->getCode();
    
    // Try to extract HTTP status code from error message if getCode() returns 0
    if ($exitcode === 0 && preg_match('/\[(\d{3})\]/', $exc->getMessage(), $matches)) {
        $exitcode = (int) $matches[1];
    }
    
    if ($exitcode === 0) {
        $exitcode = 1; // Ensure non-zero exit code on errors
    }
}

if (empty($statements) === false) {
    $downloaded = $engine->download(
        \array_key_exists(1, $argv) ? $argv[1] : Shared::cfg('STATEMENTS_DIR', getcwd()),
        $statements,
        \array_key_exists(2, $argv) ? $argv[2] : Shared::cfg('STATEMENT_FORMAT', 'pdf'),
    );
    $report = [
        'status' => 'success',
        'timestamp' => date('c'),
        'message' => _('Statements downloaded successfully'),
        'artifacts' => [
            'statements' => \is_array($downloaded) ? array_values($downloaded) : [],
        ],
        'metrics' => [
            'count' => \is_array($downloaded) ? \count($downloaded) : 0,
        ],
    ];
} else {
    $report = [
        'status' => 'error',
        'timestamp' => date('c'),
        'message' => _('No statements returned'),
        'metrics' => [
            'count' => 0,
        ],
    ];
}

$reportFile = Shared::cfg('REPORT_FILE', 'statement_report.json');
$written = file_put_contents($reportFile, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$engine->addStatusMessage(sprintf(_('Saving result to %s'), $reportFile), $written ? 'success' : 'error');

// Exit with error code if there was an error, otherwise 0 on success or 2 if write failed
if ($exitcode !== 0) {
    exit($exitcode);
}

exit($written ? 0 : 2);
