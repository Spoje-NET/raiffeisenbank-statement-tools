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


if (ApiClient::checkCertificatePresence(Shared::cfg('CERT_FILE'), true) === false) {
    $engine->addStatusMessage(sprintf(_('Certificate file %s problem'), Shared::cfg('CERT_FILE')), 'error');

    exit(1);
}

$engine->setScope(Shared::cfg('REPORT_SCOPE', 'yesterday'));

if (Shared::cfg('APP_DEBUG', false)) {
    $engine->logBanner($engine->get);
}

try {
    $status = 'ok';
    $exitcode = 0;
    $statements = $engine->getStatements(Shared::cfg('ACCOUNT_CURRENCY', 'CZK'), Shared::cfg('STATEMENT_LINE', 'ADDITIONAL'));
} catch (\VitexSoftware\Raiffeisenbank\ApiException $exc) {
    $status = $exc->getCode().': error';
    $exitcode = (int) $exc->getCode();
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
    $payments['status'] = 'statement '.$statements[0]->statementId;

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
} else {
    if ($exitcode === 0) {
        $payments['status'] = 'no statements returned';
    }
}

$written = file_put_contents($destination, json_encode($payments, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode ?: ($written ? 0 : 2));
