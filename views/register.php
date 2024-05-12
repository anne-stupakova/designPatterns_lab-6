<?php
namespace Memento;

require_once 'db/DatabaseConnection.php';
require_once 'models/userMod.php';
require_once 'PasswordStrategy.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Невірний формат електронної пошти.";
    } elseif (strlen($password) < 4) {
        $error = "Пароль повинен містити принаймні 4 символи.";
    } else {
        $passwordManager = new PasswordManager();
        $passwordManager->setStrategy(new HashedPasswordStrategy());

        $hashedPassword = $passwordManager->hashPassword($password);

        $user = new UserMod($name, $email, $hashedPassword);

        $dbConnection = DatabaseConnection::getInstance();
        $connection = $dbConnection->getConnection();

        $stmtCheckEmail = $connection->prepare("SELECT id FROM users WHERE email = :email");
        $stmtCheckEmail->bindParam(':email', $email);
        $stmtCheckEmail->execute();
        $existingUser = $stmtCheckEmail->fetch(\PDO::FETCH_ASSOC);

        if ($existingUser) {
            $error = "Користувач з такою електронною поштою вже існує.";
        } else {
            $stmt = $connection->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");

            $nameValue = $user->getName();
            $emailValue = $user->getEmail();

            $stmt->bindParam(':name', $nameValue);
            $stmt->bindParam(':email', $emailValue);
            $stmt->bindParam(':password', $hashedPassword);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $connection->lastInsertId();
                header("Location: login.php");
                exit;
            } else {
                $error = "Реєстрація не вдалася. Будь ласка, спробуйте ще раз.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../styles/authorisation.css">
    <link rel="stylesheet" href="../styles/wrapper.css">
</head>
<body>
<?php include 'wrapper/header.php'; ?>

<main class="container">
    <h1>Реєстрація</h1>
    <?php if (isset($error)) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="name">Ім'я:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>

        <p>Вже маєте акаунт? <a href="login.php">Вхід</a>.</p>

        <button type="submit">Зареєструватися</button>
    </form>
</main>

<?php include 'wrapper/footer.php'; ?>
</body>
</html>
