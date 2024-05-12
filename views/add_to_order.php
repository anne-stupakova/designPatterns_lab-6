<?php
namespace Memento;

session_start();

require_once 'db/DatabaseConnection.php';
require_once 'models/orderMod.php';

abstract class Command {
    protected $app;
    protected $editor;

    public function __construct($app, $editor) {
        $this->app = $app;
        $this->editor = $editor;
    }

    public abstract function execute();
    public abstract function undo();
}

class SubmitOrderCommand extends Command {
    private $city;
    private $departmentNumber;
    private $phone;
    private $userId;
    private $productId;
    private $count;
    private $allPrice;
    private $error;

    public function __construct($app, $editor, $city, $departmentNumber, $phone, $userId, $productId, $count, $allPrice) {
        parent::__construct($app, $editor);
        $this->city = $city;
        $this->departmentNumber = $departmentNumber;
        $this->phone = $phone;
        $this->userId = $userId;
        $this->productId = $productId;
        $this->count = $count;
        $this->allPrice = $allPrice;
    }

    public function execute() {
        if (!preg_match("/^\d{1,5}$/", $this->departmentNumber)) {
            $this->error = "Номер відділення повинен містити від 1 до 5 цифр.";
            return false;
        }

        $phonePattern = "/^\+?(\d{1,3})?\s?\(?(\d{3})\)?[\s.-]?(\d{2})[\s.-]?(\d{2,3})[\s.-]?(\d{2,3})$/";
        if (!preg_match($phonePattern, $this->phone)) {
            $this->error = "Невірний формат номера телефону.";
            return false;
        }

        try {
            $stmtInsert = $this->app->getConnection()->prepare("INSERT INTO orders (city, num_department, phone, id_user, id_goods, count, all_price) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmtInsert->bindParam(1, $this->city);
            $stmtInsert->bindParam(2, $this->departmentNumber);
            $stmtInsert->bindParam(3, $this->phone);
            $stmtInsert->bindParam(4, $this->userId);
            $stmtInsert->bindParam(5, $this->productId);
            $stmtInsert->bindParam(6, $this->count);
            $stmtInsert->bindParam(7, $this->allPrice);

            $stmtInsert->execute();

            header("Location: index.php");
            exit;
        } catch (\Exception $e) {
            $this->error = "Помилка збереження замовлення: " . $e->getMessage();
            return false;
        }

        return true;
    }

    public function undo() {
        try {
            $stmtDelete = $this->app->getConnection()->prepare("DELETE FROM orders WHERE id_user = ? AND id_goods = ?");
            $stmtDelete->bindParam(1, $this->userId);
            $stmtDelete->bindParam(2, $this->productId);
            $stmtDelete->execute();

            header("Location: index.php");
            exit;
        } catch (\Exception $e) {
            $this->error = "Помилка відміни замовлення: " . $e->getMessage();
            return false;
        }

        return true;
    }

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

$dbConnection = DatabaseConnection::getInstance();
$connection = $dbConnection->getConnection();
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
    $stmtPrice->bindParam(1, $productId);
    $stmtPrice->execute();
    $priceData = $stmtPrice->fetch(\PDO::FETCH_ASSOC);

    if ($priceData) {
        $price = $priceData['price'];
        $allPrice = $price * $count;

        $command = new SubmitOrderCommand($app, null, $city, $departmentNumber, $phone, $userId, $productId, $count, $allPrice);

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
<?php include 'wrapper/header.php'; ?>

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
<?php include 'wrapper/footer.php'; ?>
</body>
</html>
