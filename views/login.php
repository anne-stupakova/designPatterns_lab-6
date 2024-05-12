<?php
namespace Memento;

require_once '../db/DatabaseConnection.php';
require_once '../models/userMod.php';
require_once 'PasswordStrategy.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Будь ласка, заповніть усі поля.";
    } else {
        $connection = DatabaseConnection::getInstance()->getConnection();

        $stmt = $connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            $passwordManager = new PasswordManager();
            $passwordManager->setStrategy(new ReversiblePasswordStrategy());

            if ($passwordManager->verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Неправильна адреса електронної пошти або пароль.";
            }
        } else {
            $error = "Неправильна адреса електронної пошти або пароль.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../styles/authorisation.css">
    <link rel="stylesheet" href="../styles/wrapper.css">
</head>
<body>
<?php include '../wrapper/header.php'; ?>

<main class="container">
    <h1>Увійти</h1>
    <?php if (isset($error)) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>
    <form method="post" action="<?php echo SELF_URL; ?>">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>

        <p>Ще не маєте акаунт? <a href="register.php">Зареєструватися</a>.</p>

        <button type="submit">Увійти</button>
    </form>
</main>

<?php include '../wrapper/footer.php'; ?>
</body>
</html>
