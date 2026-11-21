<?php
//演習や動作テストの際に、いつでも todos テーブルのデータを全件削除して綺麗な状態（初期サンプルデータが入った状態）に戻せる 初期化スクリプト（crud/reset.php）

require_once __DIR__ . '/../config/supabase.php';

try {
    echo "--- todos テーブルの初期化処理を開始します ---" . PHP_EOL;

    // 1. cURL で Supabase の RPC (SQL関数) エンドポイントを呼び出し、全削除＆IDリセット
    $rpcUrl = $_ENV['SUPABASE_URL'] . '/rest/v1/rpc/reset_todos_table_rpc';
    $apiKey = $_ENV['SUPABASE_KEY'];

    $ch = curl_init($rpcUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . $apiKey,
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        throw new Exception("RPC実行エラー (HTTP {$httpCode}): {$response}");
    }

    echo "🗑️ 既存データを全削除し、IDを 1 にリセットしました。" . PHP_EOL;

    // 2. 初期データ（シードデータ）のセット
    $db = $service->initializeDatabase('todos', 'id');
    $initialData = [
        ['title' => '環境構築を完了する', 'is_complete' => true],
        ['title' => 'PHPからSupabaseのCRUD操作をテストする', 'is_complete' => false],
    ];

    foreach ($initialData as $data) {
        $db->insert($data);
    }

    echo "🌱 初期サンプルデータ（" . count($initialData) . " 件）を登録しました！" . PHP_EOL;

    // 3. 初期化後のデータ一覧を表示
    echo PHP_EOL . "--- 初期化後のタスク一覧 ---" . PHP_EOL;
    $currentTodos = $db->fetchAll()->getResult();
    foreach ($currentTodos as $todo) {
        $status = $todo->is_complete ? '【完了】' : '【未完了】';
        echo "ID: {$todo->id} | {$status} {$todo->title}" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "初期化エラー: " . $e->getMessage() . PHP_EOL;
}
