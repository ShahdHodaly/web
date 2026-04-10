<?php
// orders-array.php

$orders = [
    1 => [
        'order_number' => 'ORD-001',
        'customer' => 'Sarah Ahmed',
        'customer_email' => 'sarah.a@email.com',
        'user_id' => 1,
        'date' => '2024-03-15 14:30:00',
        'total' => 156.50,
        'status' => 'completed',
        'payment_method' => 'Credit Card',
        'items_count' => 3,
        'notes' => 'Please gift wrap',
        'is_gift' => true,
        'gift_message' => 'Happy Birthday Sarah! Hope you love this teddy bear. Love, Mom ❤️',
        'gift_box' => 'heartsbag.png',
        'gift_wrap_price' => 5.00,
        'products' => [
            ['name' => 'Barbie Adventure', 'price' => 45.99, 'quantity' => 1, 'image' => 'barbie5.png'],
            ['name' => 'Cute Giraffe', 'price' => 55.25, 'quantity' => 2, 'image' => 'teddy2.png']
        ]
    ],
    2 => [
        'order_number' => 'ORD-002',
        'customer' => 'Mohamed Ali',
        'customer_email' => 'm.ali@email.com',
        'user_id' => 2,
        'date' => '2024-03-15 09:15:00',
        'total' => 89.99,
        'status' => 'processing',
        'payment_method' => 'PayPal',
        'items_count' => 1,
        'notes' => '',
        'is_gift' => false,
        'gift_message' => '',
        'gift_box' => '',
        'gift_wrap_price' => 0,
        'products' => [
            ['name' => 'Building Blocks Set', 'price' => 89.99, 'quantity' => 1, 'image' => 'building1.png']
        ]
    ],
    3 => [
        'order_number' => 'ORD-003',
        'customer' => 'Noor Hassan',
        'customer_email' => 'noor.h@email.com',
        'user_id' => 3,
        'date' => '2024-03-14 10:45:00',
        'total' => 234.75,
        'status' => 'pending',
        'payment_method' => 'Bank Transfer',
        'items_count' => 4,
        'notes' => 'Call before delivery',
        'is_gift' => true,
        'gift_message' => 'To my dearest Noor, wishing you a wonderful birthday filled with joy! From your friend, Lina',
        'gift_box' => 'box.png',
        'gift_wrap_price' => 4.00,
        'products' => [
            ['name' => 'Classic Barbie', 'price' => 70.00, 'quantity' => 2, 'image' => 'barbie4.png'],
            ['name' => 'Sports Car Toy', 'price' => 45.50, 'quantity' => 1, 'image' => 'car1.png'],
            ['name' => 'Jenga Game', 'price' => 19.25, 'quantity' => 1, 'image' => 'group2.png']
        ]
    ],
    4 => [
        'order_number' => 'ORD-004',
        'customer' => 'Lina Mahmoud',
        'customer_email' => 'lina.m@email.com',
        'user_id' => 4,
        'date' => '2024-03-14 16:20:00',
        'total' => 312.00,
        'status' => 'shipped',
        'payment_method' => 'Credit Card',
        'items_count' => 5,
        'notes' => 'Express shipping',
        'is_gift' => true,
        'gift_message' => 'Happy Anniversary! Love you forever ❤️',
        'gift_box' => 'teddywrap.png',
        'gift_wrap_price' => 6.00,
        'products' => [
            ['name' => 'Elegant Barbie', 'price' => 48.00, 'quantity' => 1, 'image' => 'barbie2.png'],
            ['name' => 'Xylophone Toy', 'price' => 42.00, 'quantity' => 2, 'image' => 'kids3.png'],
            ['name' => 'Puzzle House', 'price' => 36.00, 'quantity' => 2, 'image' => 'puzzles3.png']
        ]
    ],
    5 => [
        'order_number' => 'ORD-005',
        'customer' => 'Omar Farouk',
        'customer_email' => 'omar.f@email.com',
        'user_id' => 5,
        'date' => '2024-03-13 11:30:00',
        'total' => 67.50,
        'status' => 'cancelled',
        'payment_method' => 'PayPal',
        'items_count' => 2,
        'notes' => 'Customer requested cancellation',
        'is_gift' => false,
        'gift_message' => '',
        'gift_box' => '',
        'gift_wrap_price' => 0,
        'products' => [
            ['name' => 'Cute Bunny Teddy', 'price' => 28.00, 'quantity' => 1, 'image' => 'teddy1.png'],
            ['name' => 'Ludo Game', 'price' => 39.50, 'quantity' => 1, 'image' => 'group3.png']
        ]
    ],
    6 => [
        'order_number' => 'ORD-006',
        'customer' => 'Fatima Zayed',
        'customer_email' => 'fatima.z@email.com',
        'user_id' => 6,
        'date' => '2026-03-12 13:10:00',
        'total' => 178.25,
        'status' => 'processing',
        'payment_method' => 'Credit Card',
        'items_count' => 3,
        'notes' => '',
        'is_gift' => true,
        'gift_message' => 'Congratulations on your new baby! Wishing you lots of joy!',
        'gift_box' => 'heartsbag.png',
        'gift_wrap_price' => 5.00,
        'products' => [
            ['name' => 'Brown Gift Teddy', 'price' => 44.00, 'quantity' => 2, 'image' => 'teddy3.png'],
            ['name' => 'Fire Truck', 'price' => 90.25, 'quantity' => 1, 'image' => 'car2.png']
        ]
    ],
    7 => [
        'order_number' => 'ORD-007',
        'customer' => 'Youssef Ibrahim',
        'customer_email' => 'youssef.i@email.com',
        'user_id' => 7,
        'date' => '2024-03-11 09:00:00',
        'total' => 423.99,
        'status' => 'delivered',
        'payment_method' => 'Credit Card',
        'items_count' => 6,
        'notes' => 'Birthday gift',
        'is_gift' => false,
        'gift_message' => '',
        'gift_box' => '',
        'gift_wrap_price' => 0,
        'products' => [
            ['name' => 'Collector Barbie', 'price' => 55.00, 'quantity' => 1, 'image' => 'barbie3.png'],
            ['name' => 'Mega Building Set', 'price' => 60.00, 'quantity' => 1, 'image' => 'building5.png'],
            ['name' => 'Modern SUV', 'price' => 60.00, 'quantity' => 2, 'image' => 'car5.png'],
            ['name' => 'Family Card Game', 'price' => 40.00, 'quantity' => 2, 'image' => 'group5.png']
        ]
    ]
];

// دالة لحساب إجمالي الطلب
function calculateOrderTotal($products, $gift_wrap_price = 0) {
    $total = 0;
    $items_count = 0;
    foreach ($products as $item) {
        $price = $item['price'];
        $quantity = $item['quantity'];
        $total += $price * $quantity;
        $items_count += $quantity;
    }
    $total += $gift_wrap_price;
    return ['total' => $total, 'items_count' => $items_count];
}

// تحديث الإجماليات للطلبات
foreach ($orders as $key => $order) {
    $calculated = calculateOrderTotal($order['products'], $order['gift_wrap_price'] ?? 0);
    $orders[$key]['total'] = $calculated['total'];
    $orders[$key]['items_count'] = $calculated['items_count'];
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
?>