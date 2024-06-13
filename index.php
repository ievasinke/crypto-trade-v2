<?php

require_once 'vendor/autoload.php';

use App\CoingeckoApiClient;
use App\Wallet;
use App\TransactionManager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

$cryptoCurrencies = CoingeckoApiClient::load();
$wallet = new Wallet();

while (true) {
    $outputTasks = new ConsoleOutput();
    $tableActivities = new Table($outputTasks);
    $tableActivities
        ->setHeaders(['Index', 'Action'])
        ->setRows([
            ['1', 'Show list of top currencies'],
            ['2', 'Wallet'],
            ['3', 'Buy'],
            ['4', 'Sell'],
            ['5', 'Display transaction list'],
            ['0', 'Exit'],
        ])
        ->render();
    $action = (int)readline("Enter the index of the action: ");

    if ($action === 0) {
        break;
    }

    switch ($action) {
        case 1: //Show list of top currencies
            TransactionManager::displayList($cryptoCurrencies);
            break;
        case 2: //Wallet
            TransactionManager::viewWallet($wallet);
            break;
        case 3: //Buy
            TransactionManager::buy($cryptoCurrencies, $wallet);
            break;
        case 4: //Sell
            TransactionManager::sell($cryptoCurrencies, $wallet);
            break;
        case 5: //Display transaction list
            TransactionManager::displayTransactionList('data/transactions.json');
            break;
        default:
            break;
    }
}
