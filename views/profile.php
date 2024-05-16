<?php
namespace Memento;

require_once '../db/DatabaseConnection.php';
require_once '../models/userMod.php';
require_once '../models/orderMod.php';
require_once 'PasswordStrategy.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user = null;
$connection = DatabaseConnection::getInstance()->getConnection();

$passwordManager = new PasswordManager();
$strategy = new HashedPasswordStrategy();
$passwordManager->setStrategy($strategy);

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

if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($currentPassword && $newPassword && $confirmPassword) {
        if ($passwordManager->verifyPassword($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                $hashedPassword = $passwordManager->hashPassword($newPassword);

                $stmt = $connection->prepare("UPDATE users SET password = :hashedPassword WHERE id = :userId");
                $stmt->bindParam(':hashedPassword', $hashedPassword);
                $stmt->bindParam(':userId', $_SESSION['user_id']);

                if ($stmt->execute()) {
                    header("Location: profile.php");
                    exit;
                } else {
                    $error = "Не вдалося змінити пароль.";
                }
            } else {
                $error = "Новий пароль не співпадає з підтвердженням.";
            }
        } else {
            $error = "Неправильний поточний пароль.";
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

if (isset($_POST['delete_account'])) {
    $stmt = $connection->prepare("DELETE FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $_SESSION['user_id']);
    if ($stmt->execute()) {
        session_destroy();
        header("Location: index.php");
        exit;
    } else {
        $error = "Не вдалося видалити акаунт.";
    }
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

    <?php if (isset($error)) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>

    <form method="post" action="<?php echo SELF_URL; ?>">
        <label for="new_name">Ім'я:</label>
        <input type="text" id="new_name" name="new_name" value="<?php echo $user['name']; ?>" required>

        <label for="new_email">Email:</label>
        <input type="email" id="new_email" name="new_email" value="<?php echo $user['email']; ?>" required>

        <button type="submit" name="edit">Зберегти зміни</button>
    </form>

    <form method="post" action="<?php echo SELF_URL; ?>">
        <label for="current_password">Поточний пароль:</label>
        <input type="password" id="current_password" name="current_password" required>

        <label for="new_password">Новий пароль:</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Підтвердіть новий пароль:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" name="change_password">Змінити пароль</button>
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

    <form method="post" action="<?php echo SELF_URL; ?>">
        <button type="submit" class="delete_btn" name="delete_account" onclick="return confirm('Ви впевнені, що хочете видалити свій акаунт?')">Видалити акаунт</button>
    </form>

</main>

<?php include '../wrapper/footer.php'; ?>
</body>
</html>