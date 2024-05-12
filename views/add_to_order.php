<?php
namespace Memento;

session_start();

require_once '../db/DatabaseConnection.php';
require_once '../models/orderMod.php';

class Command {
    protected $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function execute() {}

    public function undo() {}
}

class SubmitOrderCommand extends Command {
    private $orderData;
    private $error;

    public function __construct($app, $orderData) {
        parent::__construct($app);
        $this->orderData = $orderData;
    }

    public function execute() {
        try {
            $stmtInsert = $this->app->getConnection()->prepare("INSERT INTO orders (city, num_department, phone, id_user, id_goods, count, all_price) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmtInsert->execute($this->orderData);

            header("Location: index.php");
            exit;
        } catch (\Exception $e) {
            $this->error = "Помилка збереження замовлення: " . $e->getMessage();
            return false;
        }

        return true;
    }

    public function undo() {}

    public function getError() {
        return $this->error;
    }
}
class Application {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getConnection() {
        return $this->connection;
    }
}
$connection = DatabaseConnection::getInstance()->getConnection();
$app = new Application($connection);

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order'])) {
    $city = isset($_POST['city']) ? $_POST['city'] : '';
    $departmentNumber = isset($_POST['department_number']) ? $_POST['department_number'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $productId = isset($_SESSION['id_goods']) ? $_SESSION['id_goods'] : null;
    $count = isset($_POST['count']) ? $_POST['count'] : 0;

    $stmtPrice = $connection->prepare("SELECT price FROM goods WHERE id = ?");
    $stmtPrice->execute([$productId]);
    $priceData = $stmtPrice->fetch(\PDO::FETCH_ASSOC);

    if ($priceData) {
        $price = $priceData['price'];
        $allPrice = $price * $count;

        $orderData = [
            $city,
            $departmentNumber,
            $phone,
            $userId,
            $productId,
            $count,
            $allPrice
        ];

        $command = new SubmitOrderCommand($app, $orderData);

        if (!$command->execute()) {
            $error = $command->getError();
        }
    } else {
        $error = "Помилка: Товар з ID $productId не знайдено або не має ціни.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення</title>
    <link rel="stylesheet" href="../styles/order.css">
    <link rel="stylesheet" href="../styles/wrapper.css">
</head>
<body>
<?php include '../wrapper/header.php'; ?>

<h1>Оформлення замовлення</h1>
<?php if (!empty($error)) { ?>
    <p class="error"><?php echo $error; ?></p>
<?php } ?>
<form action="add_to_order.php" method="post" class="container">
    <label for="city">Місто:</label>
    <input type="text" id="city" name="city" required><br><br>

    <label for="department_number">Номер відділення:</label>
    <input type="text" id="department_number" name="department_number" required><br><br>

    <label for="count">Кількість товару:</label>
    <input type="number" id="count" name="count" min="1" required><br><br>

    <label for="phone">Телефон:</label>
    <input type="tel" id="phone" name="phone" required><br><br>

    <input type="submit" name="submit_order" value="Оформити замовлення">
</form>
<?php include '../wrapper/footer.php'; ?>
</body>
</html>
