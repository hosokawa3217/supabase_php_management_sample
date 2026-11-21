<?php
// データ取得サンプル (crud/select.php)
// todos テーブルからデータを全件取得するスクリプトを作成します。

require_once __DIR__ . '/../config/supabase.php';

// 'todos' テーブルを指定（主キーは 'id'）
$db = $service->initializeDatabase('todos', 'id');

try {
    // 全件取得
    $todos = $db->fetchAll()->getResult();

    echo "--- タスク一覧 ---" . PHP_EOL;
    foreach ($todos as $todo) {
        $status = $todo->is_complete ? '【完了】' : '【未完了】';
        echo "ID: {$todo->id} | {$status} {$todo->title}" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "データ取得エラー: " . $e->getMessage() . PHP_EOL;
}