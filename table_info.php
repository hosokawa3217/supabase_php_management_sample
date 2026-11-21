<?php
/**
 * Supabase(PostgreSQL) のテーブル情報を取得して表示するスクリプト。
 *
 * 使い方: php table_info.php [スキーマ名]   (既定: public)
 *
 * スキーマ構造の漏えいを防ぐため、CLI からの実行のみ許可する。
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('このスクリプトはCLIからのみ実行できます。');
}

// .env があれば読み込む（vlucas/phpdotenv が入っている場合のみ）
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
    Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
}

// 環境変数から接続情報を取得（index.php と同じ変数名）
$env = fn(string $k, ?string $d = null) => $_ENV[$k] ?? getenv($k) ?: $d;
$host     = $env('DB_HOST', 'aws-0-ap-northeast-1.pooler.supabase.com');
$port     = $env('DB_PORT', '6543');
$dbname   = $env('DB_NAME', 'postgres');
$user     = $env('DB_USER');
$password = $env('DB_PASSWORD');
$schema   = $argv[1] ?? 'public';

try {
    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "データベース接続エラー: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

// テーブル一覧
$stmt = $pdo->prepare(
    "SELECT table_name FROM information_schema.tables
     WHERE table_schema = :s AND table_type = 'BASE TABLE'
     ORDER BY table_name"
);
$stmt->execute([':s' => $schema]);
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// カラム情報
$colStmt = $pdo->prepare(
    "SELECT column_name, data_type, is_nullable, column_default
     FROM information_schema.columns
     WHERE table_schema = :s AND table_name = :t
     ORDER BY ordinal_position"
);

// 主キー
$pkStmt = $pdo->prepare(
    "SELECT kcu.column_name
     FROM information_schema.table_constraints tc
     JOIN information_schema.key_column_usage kcu
       ON tc.constraint_name = kcu.constraint_name
      AND tc.table_schema = kcu.table_schema
     WHERE tc.constraint_type = 'PRIMARY KEY'
       AND tc.table_schema = :s AND tc.table_name = :t"
);

echo "スキーマ: {$schema} / テーブル数: " . count($tables) . PHP_EOL;

foreach ($tables as $table) {
    $colStmt->execute([':s' => $schema, ':t' => $table]);
    $pkStmt->execute([':s' => $schema, ':t' => $table]);
    $pks = $pkStmt->fetchAll(PDO::FETCH_COLUMN);

    echo PHP_EOL . "■ {$table}" . PHP_EOL;
    foreach ($colStmt->fetchAll() as $c) {
        printf(
            "  - %-20s %-25s %s%s%s\n",
            $c['column_name'],
            $c['data_type'],
            $c['is_nullable'] === 'NO' ? 'NOT NULL' : 'NULL',
            in_array($c['column_name'], $pks, true) ? ' [PK]' : '',
            $c['column_default'] !== null ? " default={$c['column_default']}" : ''
        );
    }
}
