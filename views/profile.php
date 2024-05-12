<?php
namespace Memento;

require_once '../db/DatabaseConnection.php';
require_once '../models/userMod.php';
require_once '../models/orderMod.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user = null;
$connection = DatabaseConnection::getInstance()->getConnection();

$stmt = $connection->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(\PDO::FETCH_ASSOC);

$orders = [];
$stmt = $connection->prepare("SELECT orders.*, goods.title AS product_name FROM orders 
    INNER JOIN goods ON orders.id_goods = goods.id 
    WHERE id_user = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$ordersData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

foreach ($ordersData as $orderData) {
    $order = new OrderMod(
        $orderData['id'],
        $orderData['city'],
        $orderData['num_department'],
        $orderData['phone'],
        $orderData['id_user'],
        $orderData['id_goods'],
        $orderData['product_name'],
        $orderData['count'],
        $orderData['all_price']
    );
    $orders[] = $order;
}

if (isset($_POST['edit'])) {
    $newName = $_POST['new_name'];
    $newEmail = $_POST['new_email'];

    if ($newName && $newEmail) {
        $stmt = $connection->prepare("UPDATE users SET name = :newName, email = :newEmail WHERE id = :userId");
        $stmt->bindParam(':newName', $newName);
        $stmt->bindParam(':newEmail', $newEmail);
        $stmt->bindParam(':userId', $_SESSION['user_id']);

        if ($stmt->execute()) {
            header("Location: profile.php");
            exit;
        } else {
            $error = "Не вдалося оновити профіль.";
        }
    } else {
        $error = "Будь ласка, заповніть усі поля.";
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memento</title>
    <link rel="stylesheet" href="../styles/profile.css">
    <link rel="stylesheet" href="../styles/wrapper.css">
</head>
<body>
<?php include '../wrapper/header.php'; ?>

<main class="container">
    <h1>Профіль користувача</h1>
    <h2>Привіт, <?php echo $user['name']; ?>!</h2>

    <form method="post" action="<?php echo SELF_URL; ?>">
        <label for="new_name">Ім'я:</label>
        <input type="text" id="new_name" name="new_name" value="<?php echo $user['name']; ?>" required>

        <label for="new_email">Email:</label>
        <input type="email" id="new_email" name="new_email" value="<?php echo $user['email']; ?>" required>

        <button type="submit" name="edit">Зберегти зміни</button>
    </form>

    <form method="post" action="<?php echo SELF_URL; ?>">
        <button type="submit" name="logout">Вийти з акаунту</button>
    </form>

    <h3>Ваші замовлення:</h3>
    <div class="order-list">
        <?php foreach ($orders as $order) { ?>
            <div class="order-item">
                <p>Замовлення №<?php echo $order->getId(); ?></p>
                <p>Товар: <?php echo $order->getProductName(); ?></p>
                <p>Кількість: <?php echo $order->getCount(); ?></p>
                <p>Загальна вартість: <?php echo $order->getAllPrice(); ?> грн</p>
            </div>
        <?php } ?>
    </div>

</main>

<?php include '../wrapper/footer.php'; ?>
</body>
</html>
