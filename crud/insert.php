<?php
// データ追加サンプル (crud/insert.php)
// 新規タスクを追加するスクリプトを作成します。

require_once __DIR__ . '/../config/supabase.php';

$db = $service->initializeDatabase('todos', 'id');

try {
    $newTodoData = [
        'title' => 'PHPから追加したテストタスク',
        'is_complete' => false
    ];

    // データ挿入
    $result = $db->insert($newTodoData);

    echo "✅ タスクの追加に成功しました！" . PHP_EOL;
    print_r($result);
} catch (Exception $e) {
    echo "データ追加エラー: " . $e->getMessage() . PHP_EOL;
}