<?php
namespace Memento;

session_start();

require_once 'db/DatabaseConnection.php';
require_once 'models/goodsMod.php';
require_once 'models/categoriesMod.php';

interface DataFetcher {
    public function fetchCategories();
    public function fetchProducts();
}

$loggedIn = isset($_SESSION['user_id']);

class DatabaseFetcher implements DataFetcher {
    private $connection;

    public function __construct(\PDO $connection) {
        $this->connection = $connection;
    }

    public function fetchCategories() {
        $stmtCategories = $this->connection->query("SELECT * FROM categories");
        return $stmtCategories->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchProducts() {
        $stmtProducts = $this->connection->query("SELECT * FROM goods");
        return $stmtProducts->fetchAll(\PDO::FETCH_ASSOC);
    }
}

class ProxyFetcher implements DataFetcher {
    private $realFetcher;
    private $categoriesCache;
    private $productsCache;

    public function __construct(DataFetcher $realFetcher) {
        $this->realFetcher = $realFetcher;
        $this->categoriesCache = [];
        $this->productsCache = [];
    }

    public function fetchCategories() {
        if (empty($this->categoriesCache)) {
            $this->categoriesCache = $this->realFetcher->fetchCategories();
        }
        return $this->categoriesCache;
    }

    public function fetchProducts() {
        if (empty($this->productsCache)) {
            $this->productsCache = $this->realFetcher->fetchProducts();
        }
        return $this->productsCache;
    }
}

$dbConnection = DatabaseConnection::getInstance();
$connection = $dbConnection->getConnection();
$fetcher = new ProxyFetcher(new DatabaseFetcher($connection));

function getCategoriesAndProducts(DataFetcher $fetcher) {
    $categoriesData = $fetcher->fetchCategories();
    $productsData = $fetcher->fetchProducts();
    $categories = [];

    foreach ($categoriesData as $categoryData) {
        $category = new CategoriesMod($categoryData['id'], $categoryData['title']);

        foreach ($productsData as $productData) {
            if ($productData['id_categories'] == $categoryData['id']) {
                $product = new GoodsMod(
                    $productData['id'],
                    $productData['title'],
                    $productData['price'],
                    $productData['info'],
                    $productData['photo'],
                    $productData['data_creation'],
                    $productData['id_categories']
                );
                $category->addProduct($product);
            }
        }

        $categories[] = $category;
    }

    return $categories;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_goods'])) {
    $_SESSION['id_goods'] = $_POST['id_goods'];

    header("Location: add_to_order.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memento</title>
    <link rel="stylesheet" href="../styles/index.css">
    <link rel="stylesheet" href="../styles/wrapper.css">
</head>
<body>
<?php include 'wrapper/header.php'; ?>

<main class="container">
    <?php
    $categories = getCategoriesAndProducts($fetcher);

    foreach ($categories as $category) {
        echo "<section class='category'>";
        echo "<h2>" . $category->getTitle() . "</h2>";
        echo "<div class='product-container'>";

        foreach ($category->getProducts() as $product) {
            echo "<article class='product'>";
            echo "<h3>" . $product->getName() . "</h3>";
            echo "<p>Ціна: " . $product->getPrice() . " грн</p>";
            echo "<p>Опис: " . $product->getInfo() . "</p>";
            $imagePath = 'img_goods/' . $product->getPhoto();
            echo "<img src='" . $imagePath . "' alt='" . $product->getName() . "'>";

            if ($loggedIn) {
                echo "<div class='add-to-orders-wrapper'>";
                echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                echo "<input type='hidden' name='id_goods' value='" . $product->getId() . "'>";
                echo "<button class='add-to-order-btn' type='submit'>Замовити</button>";
                echo "</form>";
                echo "</div>";
            } else {
                echo "<p>Зареєструйтесь, щоб замовити товар</p>";
            }
            echo "</article>";
        }

        echo "</div>";
        echo "</section>";
    }
    ?>
</main>

<?php include 'wrapper/footer.php'; ?>
</body>
</html>
