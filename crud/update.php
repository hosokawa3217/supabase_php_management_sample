<?php
//データ更新サンプル (crud/update.php)

require_once __DIR__ . '/../config/supabase.php';

// 'todos' テーブルを指定
$db = $service->initializeDatabase('todos', 'id');

try {
    // IDが 1 のデータの status (is_complete) を true (完了) に更新
    $result = $db->update('1', ['is_complete' => true]);

    echo "✅ ID: 1 のタスクを【完了】に更新しました！" . PHP_EOL;
    print_r($result);
} catch (Exception $e) {
    echo "更新エラー: " . $e->getMessage() . PHP_EOL;
}