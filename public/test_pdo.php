<?php
$host = '127.0.0.1';
$db   = 'evo';
$user = 'root'; // default MAMP user
$pass = 'root'; // default MAMP pass
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=8889"; // MAMP default port 8889
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check tx 20
    $stmt = $pdo->query('SELECT * FROM transactions WHERE id = 20');
    $tx = $stmt->fetch();
    echo "TRANSACTION 20:\n";
    print_r($tx);
    
    // Check contract
    $stmt = $pdo->query('SELECT * FROM contracts WHERE transaction_id = 20');
    $contract = $stmt->fetch();
    echo "\nCONTRACT:\n";
    print_r($contract);

    // Check statements
    $stmt = $pdo->query('SELECT * FROM financial_statements WHERE description LIKE "%20%"');
    $fin = $stmt->fetchAll();
    echo "\nFINANCIAL STATEMENTS:\n";
    print_r($fin);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
