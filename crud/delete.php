<?php
//削除サンプル (crud/delete.php)

require_once __DIR__ . '/../config/supabase.php';

// 'todos' テーブルを指定
$db = $service->initializeDatabase('todos', 'id');

try {
    // IDが 1 のデータを削除
    $result = $db->delete('1');

    echo "🗑️ ID: 1 のタスクを削除しました！" . PHP_EOL;
} catch (Exception $e) {
    echo "削除エラー: " . $e->getMessage() . PHP_EOL;
}