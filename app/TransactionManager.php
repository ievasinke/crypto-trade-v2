<?php

namespace App;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use function Clue\StreamFilter\append;

class TransactionManager
{
    public static function displayList($cryptoCurrencies): void
    {
        $outputCrypto = new ConsoleOutput();
        $tableCryptoCurrencies = new Table($outputCrypto);
        $tableCryptoCurrencies
            ->setHeaders(['Index', 'Name', 'Symbol', 'Price']);
        $tableCryptoCurrencies
            ->setRows(array_map(function (int $index, CryptoCurrency $cryptoCurrency): array {
                return [
                    $index + 1,
                    $cryptoCurrency->getName(),
                    $cryptoCurrency->getSymbol(),
                    new TableCell(
                        number_format($cryptoCurrency->getPrice(), 4),
                        ['style' => new TableCellStyle(['align' => 'right',])]
                    ),

                ];
            }, array_keys($cryptoCurrencies), $cryptoCurrencies));
        $tableCryptoCurrencies->setStyle('box-double');
        $tableCryptoCurrencies->render();
    }

    public static function viewWallet(Wallet $wallet): void
    {
        $output = new ConsoleOutput();
        $tableWallet = new Table($output);
        $tableWallet
            ->setHeaders(['Currency', 'Quantity', 'Total amount (USD)']);
        $tableWallet
            ->setRows(array_map(function (string $symbol, array $details): array {
                return [
                    $symbol,
                    $details['quantity'],
                    number_format($details['totalAmount'], 2)
                ];
            }, array_keys($wallet->getPortfolio()), $wallet->getPortfolio()));
        $tableWallet->setStyle('box');
        $tableWallet->render();
        $total = number_format($wallet->getBalance(), 2);
        echo "You have \$$total in your wallet\n";
    }

    public static function buyCrypto(array $cryptoCurrencies, Wallet $wallet, string $transactionFile): void
    {
        $index = (int)readline("Enter the index of the crypto currency to buy: ") - 1;
        $quantity = (float)readline("Enter the quantity: ");
        $type = 'buy';

        if (isset($cryptoCurrencies[$index])) {
            $currency = $cryptoCurrencies[$index];
            $price = $currency->getPrice();
            $totalAmount = $price * $quantity;

            try {
                $wallet->addCrypto($currency->getSymbol(), $quantity, $price);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            self::logTransaction(
                'buy',
                $currency->getName(),
                $currency->getSymbol(),
                $quantity,
                $price,
            );
            echo "You bought {$currency->getName()} for \$$totalAmount\n";
        } else {
            echo "Invalid index.";
        }
    }

    private static function logTransaction(
        string $type,
        string $currency,
        string $symbol,
        float  $quantity,
        float  $price,
        string $transactionsFile = 'data/transactions.json'
    ): void
    {
        $transactions = file_exists($transactionsFile) ? json_decode(file_get_contents($transactionsFile)) : [];
        $transactions[] = new Transaction($type, $currency, $symbol, $quantity, $price);
        file_put_contents($transactionsFile, json_encode($transactions), FILE_APPEND);
    }
}