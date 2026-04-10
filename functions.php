<?php
// functions.php

// دالة لجلب إحصائيات المستخدم من الطلبات
function getUserStats($user_id, $orders) {
    $total_orders = 0;
    $total_spent = 0;

    foreach ($orders as $order) {
        if (isset($order['user_id']) && $order['user_id'] == $user_id) {
            $total_orders++;
            $total_spent += $order['total'];
        }
    }

    return [
        'orders' => $total_orders,
        'spent' => $total_spent
    ];
}

// دالة لجلب طلبات المستخدم
function getUserOrders($user_id, $orders) {
    $user_orders = [];
    foreach ($orders as $id => $order) {
        if (isset($order['user_id']) && $order['user_id'] == $user_id) {
            $user_orders[$id] = $order;
        }
    }
    // ترتيب من الأحدث للأقدم
    uasort($user_orders, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    return $user_orders;
}

// دالة لتحديث إحصائيات المستخدم في الجدول (لصفحة المستخدمين)
function updateUsersWithStats($users, $orders) {
    $updated_users = [];
    foreach ($users as $id => $user) {
        $stats = getUserStats($id, $orders);
        $updated_users[$id] = array_merge($user, $stats);
    }
    return $updated_users;
}
?>