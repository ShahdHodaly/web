<?php
/* PHP: قاعدة بيانات المنتجات - القيم صفرية مؤقتة */
$products = [
    // === Teddy Bears ===
    1 => [
        "name" => "Cute Giraffe",
        "price" => "25",
        "category" => "Teddy Bear",
        "description" => "Soft and cuddly giraffe perfect for kids and gifts.",
        "image" => "images/teddy2.png",
        "sales_count" => 1,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    2 => [
        "name" => "Cute Bunny Teddy",
        "price" => "28",
        "category" => "Teddy Bear",
        "description" => "Lovely bunny style plush toy made from premium fabric.",
        "image" => "images/teddy1.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    3 => [
        "name" => "Brown Gift Teddy",
        "price" => "22",
        "category" => "Teddy Bear",
        "description" => "Perfect teddy bear for birthdays and special occasions.",
        "image" => "images/teddy3.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    4 => [
        "name" => "Hello Kitty Plush",
        "price" => "26",
        "category" => "Teddy Bear",
        "description" => "Cute cartoon plush toy for kids.",
        "image" => "images/teddy4.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    5 => [
        "name" => "Little Chick Plush",
        "price" => "20",
        "category" => "Teddy Bear",
        "description" => "Soft yellow chick toy for babies.",
        "image" => "images/teddy5.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],

    // === Dolls & Barbie ===
    6 => [
        "name" => "Barbie Princess",
        "price" => "40",
        "category" => "Dolls & Barbie",
        "description" => "Beautiful princess doll for imaginative play.",
        "image" => "images/barbie5.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    7 => [
        "name" => "Classic Barbie",
        "price" => "35",
        "category" => "Dolls & Barbie",
        "description" => "Stylish barbie doll with accessories.",
        "image" => "images/barbie4.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    8 => [
        "name" => "Fashion Barbie",
        "price" => "45",
        "category" => "Dolls & Barbie",
        "description" => "Modern fashion doll for girls.",
        "image" => "images/barbie1.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    9 => [
        "name" => "Elegant Barbie",
        "price" => "48",
        "category" => "Dolls & Barbie",
        "description" => "Premium fashion doll with elegant dress.",
        "image" => "images/barbie2.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    10 => [
        "name" => "Collector Barbie",
        "price" => "55",
        "category" => "Dolls & Barbie",
        "description" => "Special edition collectible doll.",
        "image" => "images/barbie3.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],

    // === Building Toys ===
    11 => [
        "name" => "Building Blocks Set",
        "price" => "30",
        "category" => "Building Toys",
        "description" => "Creative colorful building blocks.",
        "image" => "images/building1.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    12 => [
        "name" => "Kids House Blocks",
        "price" => "42",
        "category" => "Building Toys",
        "description" => "Fun house building toy set.",
        "image" => "images/building2.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    13 => [
        "name" => "City Builder Kit",
        "price" => "50",
        "category" => "Building Toys",
        "description" => "Advanced construction toy for kids.",
        "image" => "images/building3.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    14 => [
        "name" => "Tree House Blocks",
        "price" => "47",
        "category" => "Building Toys",
        "description" => "Creative tree house construction set.",
        "image" => "images/building4.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    15 => [
        "name" => "Mega Building Set",
        "price" => "60",
        "category" => "Building Toys",
        "description" => "Large building kit for creative minds.",
        "image" => "images/building5.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],

    // === Cars & Vehicles ===
    16 => [
        "name" => "Sports Car Toy",
        "price" => "20",
        "category" => "Cars & Vehicles",
        "description" => "Mini racing car toy.",
        "image" => "images/car1.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    17 => [
        "name" => "Luxury Car",
        "price" => "32",
        "category" => "Cars & Vehicles",
        "description" => "High speed car model.",
        "image" => "images/car3.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    18 => [
        "name" => "Fire Truck",
        "price" => "28",
        "category" => "Cars & Vehicles",
        "description" => "Realistic fire truck toy.",
        "image" => "images/car2.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    19 => [
        "name" => "Excavator Truck",
        "price" => "36",
        "category" => "Cars & Vehicles",
        "description" => "Construction vehicle toy.",
        "image" => "images/car4.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    20 => [
        "name" => "Modern SUV",
        "price" => "30",
        "category" => "Cars & Vehicles",
        "description" => "Sleek modern SUV toy model.",
        "image" => "images/car5.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],

    // === Group Games ===
    21 => [
        "name" => "Jenga Game",
        "price" => "18",
        "category" => "Group Games",
        "description" => "Fun wooden stacking game.",
        "image" => "images/group2.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    22 => [
        "name" => "Board Game Set",
        "price" => "25",
        "category" => "Group Games",
        "description" => "Perfect family group game.",
        "image" => "images/group1.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    23 => [
        "name" => "Ludo Game",
        "price" => "15",
        "category" => "Group Games",
        "description" => "Classic board game for kids.",
        "image" => "images/group3.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    24 => [
        "name" => "Twister Game",
        "price" => "22",
        "category" => "Group Games",
        "description" => "Interactive movement game.",
        "image" => "images/group4.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    25 => [
        "name" => "Family Card Game",
        "price" => "20",
        "category" => "Group Games",
        "description" => "Fun and exciting family card game.",
        "image" => "images/group5.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],

    // === Educational Toys ===
    26 => [
        "name" => "Colorful Cube",
        "price" => "14",
        "category" => "Educational Toys",
        "description" => "Cube with a different color on each face.",
        "image" => "images/kids1.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    27 => [
        "name" => "Wooden Learning Toy",
        "price" => "19",
        "category" => "Educational Toys",
        "description" => "Educational wooden activity toy.",
        "image" => "images/kids2.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    28 => [
        "name" => "Xylophone Toy",
        "price" => "21",
        "category" => "Educational Toys",
        "description" => "Musical learning toy.",
        "image" => "images/kids3.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    29 => [
        "name" => "Drawing Board",
        "price" => "17",
        "category" => "Educational Toys",
        "description" => "Magnetic drawing board.",
        "image" => "images/kids4.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    30 => [
        "name" => "Color Toy",
        "price" => "23",
        "category" => "Educational Toys",
        "description" => "Colorful learning toy.",
        "image" => "images/kids5.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],

    // === Puzzles ===
    31 => [
        "name" => "Puzzle House",
        "price" => "18",
        "category" => "Puzzles",
        "description" => "Creative puzzle set.",
        "image" => "images/puzzles3.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    32 => [
        "name" => "Nature Puzzle",
        "price" => "20",
        "category" => "Puzzles",
        "description" => "Beautiful scenery puzzle.",
        "image" => "images/puzzles4.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    33 => [
        "name" => "Cartoon Puzzle",
        "price" => "16",
        "category" => "Puzzles",
        "description" => "Kids cartoon puzzle.",
        "image" => "images/puzzles1.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    34 => [
        "name" => "Adventure Puzzle",
        "price" => "19",
        "category" => "Puzzles",
        "description" => "Adventure theme puzzle.",
        "image" => "images/puzzles2.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ],
    35 => [
        "name" => "Biscuit Set",
        "price" => "21",
        "category" => "Puzzles",
        "description" => "Delicious pretend play biscuits.",
        "image" => "images/puzzles5.png",
        "sales_count" => 0,
        "avg_rating" => 0,
        "created_at" => "2023-01-01 10:00:00"
    ]
];
?>