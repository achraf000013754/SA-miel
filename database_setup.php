<?php
/**
 * Database Setup Script
 * Run this ONCE to create the database and tables
 * Access: http://localhost/pfe/database_setup.php
 */

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sa_miel_store';

// Create connection without database
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo "✓ Database '$db_name' created successfully<br>";
} else {
    echo "✗ Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($db_name);
$conn->set_charset("utf8mb4");

// Create Users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
   email VARCHAR(191) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(email)
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'users' created successfully<br>";
} else {
    echo "✗ Error creating users table: " . $conn->error . "<br>";
}

// Create Products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    badge_ar VARCHAR(50),
    badge_en VARCHAR(50),
    stars INT DEFAULT 5,
    emoji VARCHAR(10),
    image_url VARCHAR(255),
    stock INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(category)
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'products' created successfully<br>";
} else {
    echo "✗ Error creating products table: " . $conn->error . "<br>";
}

// Create Orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX(status),
    INDEX(created_at)
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'orders' created successfully<br>";
} else {
    echo "✗ Error creating orders table: " . $conn->error . "<br>";
}

// Create Order Items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'order_items' created successfully<br>";
} else {
    echo "✗ Error creating order_items table: " . $conn->error . "<br>";
}

// Insert sample products
$sampleProducts = [
    ['عسل السدر الجبلي', 'Mountain Sidr Honey', 'مستخرج من مناطق جبال الأطلس...', 'Extracted from Atlas Mountains...', 2800, 'rare', 'فاخر', 'Luxury', 5],
    ['عسل الكاليتوس الطبي', 'Eucalyptus Medical Honey', 'عسل طبي ممتاز لعلاج الجهاز التنفسي...', 'Excellent medicinal honey...', 1800, 'medical', 'طبي', 'Medical', 5],
    ['عسل الجبل المتعدد', 'Multi-Flower Mountain Honey', 'خليط طبيعي من أزهار جبال الأوراس...', 'Natural blend from mountain flowers...', 2200, 'mountain', 'شعبي', 'Popular', 4],
    ['عسل الزهور البرية', 'Wild Flower Honey', 'خفيف وعطري، مجموع من السهول الخضراء...', 'Light and fragrant...', 1500, 'flower', 'خفيف', 'Light', 4],
];
foreach ($sampleProducts as $product) {
    // 1. تأكد من أن عدد علامات الـ (?) يطابق عدد المتغيرات التي سترسلها (هنا 8 متغيرات)
    $sql = "INSERT INTO products (name_ar, name_en, description_ar, description_en, price, category, badge_ar, badge_en, emoji, stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, '🍯', 100)"; 
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "✗ Error preparing statement: " . $conn->error . "<br>";
        continue;
    }
    
    // 2. هنا لديك 8 متغيرات، لذا يجب أن يكون هناك 8 علامات (?) في الأعلى و 8 حروف تعريف نوع البيانات
    // s = string, d = double (للسعر)
    $stmt->bind_param("ssssdsss", 
        $product[0], // name_ar
        $product[1], // name_en
        $product[2], // description_ar
        $product[3], // description_en
        $product[4], // price
        $product[5], // category
        $product[6], // badge_ar
        $product[7]  // badge_en
    );
    
    if ($stmt->execute()) {
        echo "✓ Product inserted: " . $product[0] . "<br>";
    } else {
        echo "✗ Error inserting product: " . $stmt->error . "<br>";
    }
}

echo "<hr><h3>✓ Database setup completed successfully!</h3>";
echo "<p>You can now use the API endpoints. Access the database at:</p>";
echo "<p><strong>phpMyAdmin:</strong> <a href='http://localhost/phpmyadmin/' target='_blank'>http://localhost/phpmyadmin/</a></p>";

$conn->close();
?>
