<?php

namespace App\Contracts;

interface FinanceRepositoryInterface
{
    public function createUser(array $data): array;

    public function findUser(int $id): ?array;

    public function saveTransaction(array $data): array;

    public function listTransactions(int $userId): array;

    public function deleteTransaction(int $id): void;

    public function exportToCsv(int $userId): string;

    public function exportToPdf(int $userId): string;

    public function exportToXml(int $userId): string;

    public function importFromCsv(int $userId, string $content): int;

    public function sendEmailReport(int $userId): bool;

    public function sendSmsReport(int $userId): bool;

    public function calculateIncomeTax(int $userId, int $year): float;

    public function generateMonthlyChart(int $userId): string;

    public function backupDatabase(): string;
}
