<?php
namespace Memento;

session_start();

require_once '../db/DatabaseConnection.php';
require_once '../models/goodsMod.php';
require_once '../models/categoriesMod.php';

$loggedIn = isset($_SESSION['user_id']);

interface DataFetcher {
    public function fetchCategories();
    public function fetchProducts($sortOrder);
    public function fetchProductsForCategory($categoryId, $sortOrder);
}
class DatabaseFetcher implements DataFetcher {
    private $connection;

    public function __construct(\PDO $connection) {
        $this->connection = $connection;
    }
    public function fetchCategories() {
        $stmtCategories = $this->connection->query("SELECT * FROM categories");
        return $stmtCategories->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function fetchProducts($sortOrder) {
        $orderBy = $sortOrder === 'asc' ? 'ASC' : 'DESC';
        $stmtProducts = $this->connection->query("SELECT * FROM goods ORDER BY price $orderBy");
        return $stmtProducts->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function fetchProductsForCategory($categoryId, $sortOrder) {
        $orderBy = $sortOrder === 'asc' ? 'ASC' : 'DESC';
        $stmt = $this->connection->prepare("SELECT * FROM goods WHERE id_categories = :category_id ORDER BY price $orderBy");
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
    public function fetchProducts($sortOrder) {
        if (empty($this->productsCache)) {
            $this->productsCache = $this->realFetcher->fetchProducts($sortOrder);
        }
        return $this->productsCache;
    }
    public function fetchProductsForCategory($categoryId, $sortOrder) {
        return $this->realFetcher->fetchProductsForCategory($categoryId, $sortOrder);
    }
}

$connection = DatabaseConnection::getInstance()->getConnection();
$fetcher = new ProxyFetcher(new DatabaseFetcher($connection));

$sortOrder = isset($_GET['sort']) && in_array($_GET['sort'], ['asc', 'desc']) ? $_GET['sort'] : 'asc';

function getCategoriesAndProducts(DataFetcher $fetcher, $sortOrder) {
    $categoriesData = $fetcher->fetchCategories();
    $productsData = $fetcher->fetchProducts($sortOrder);
    $categories = [];

    $selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
    $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

    foreach ($categoriesData as $categoryData) {
        $category = new CategoriesMod($categoryData['id'], $categoryData['title']);
        $categoryHasProducts = false;

        foreach ($productsData as $productData) {
            if ($productData['id_categories'] == $categoryData['id'] &&
                (empty($searchQuery) || strpos($productData['title'], $searchQuery) !== false) &&
                ($selectedCategory === '' || $productData['id_categories'] == $selectedCategory)) {
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
                $categoryHasProducts = true;
            }
        }

        if (empty($searchQuery) || $categoryHasProducts) {
            $categories[] = $category;
        }
    }

    return $categories;
}
$categories = getCategoriesAndProducts($fetcher, $sortOrder);

$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

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
<?php include '../wrapper/header.php'; ?>

<main class="container">
    <form method="GET" action="<?php echo SELF_URL; ?>" class="category-filter">
        <div class="select-wrapper">
            <select name="category" onchange="this.form.submit()">
                <option value="">Оберіть категорію</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->getId(); ?>" <?php if ($selectedCategory == $category->getId()) echo 'selected'; ?>>
                        <?php echo $category->getTitle(); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <a href="<?php echo SELF_URL; ?>" class="reset-search">Всі товари</a>
    </form>

    <form method="GET" action="<?php echo SELF_URL; ?>" class="search-form">
        <div class="search-input-wrapper">
            <input type="text" name="search" placeholder="Пошук за назвою" class="search-input">
            <button type="submit" class="search-btn">Пошук</button>
        </div>
        <a href="<?php echo isset($_GET['search']) ? SELF_URL : '#'; ?>" class="reset-search">Скинути</a>
    </form>

    <form method="GET" action="<?php echo SELF_URL; ?>" class="sort-form">
        <input type="hidden" name="category" value="<?php echo $selectedCategory; ?>">
        <label for="sort">Сортувати за ціною:</label>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="asc" <?php if ($sortOrder === 'asc') echo 'selected'; ?>>Зростання</option>
            <option value="desc" <?php if ($sortOrder === 'desc') echo 'selected'; ?>>Спадання</option>
        </select>
    </form>

    <?php
    if (!empty($selectedCategory)) {
        $selectedCategoryData = array_filter($categories, function($cat) use ($selectedCategory) {
            return $cat->getId() == $selectedCategory;
        });

        foreach ($selectedCategoryData as $category) {
            echo "<section class='category'>";
            echo "<div class='product-container'>";
            foreach ($category->getProducts() as $product) {
                echo "<article class='product'>";
                echo "<h3>" . $product->getName() . "</h3>";
                echo "<p>Ціна: " . $product->getPrice() . " грн</p>";
                echo "<p>Опис: " . $product->getInfo() . "</p>";
                $imagePath = '../img_goods/' . $product->getPhoto();
                echo "<img src='" . $imagePath . "' alt='" . $product->getName() . "'>";

                if ($loggedIn) {
                    echo "<div class='add-to-orders-wrapper'>";
                    echo "<form method='post' action='" . SELF_URL . "'>";
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
    } else {
        foreach ($categories as $category) {
            echo "<section class='category'>";
            echo "<div class='product-container'>";
            foreach ($category->getProducts() as $product) {
                echo "<article class='product'>";
                echo "<h3>" . $product->getName() . "</h3>";
                echo "<p>Ціна: " . $product->getPrice() . " грн</p>";
                echo "<p>Опис: " . $product->getInfo() . "</p>";
                $imagePath = '../img_goods/' . $product->getPhoto();
                echo "<img src='" . $imagePath . "' alt='" . $product->getName() . "'>";

                if ($loggedIn) {
                    echo "<div class='add-to-orders-wrapper'>";
                    echo "<form method='post' action='" . SELF_URL . "'>";
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
    }
    ?>
</main>

<?php include '../wrapper/footer.php'; ?>
</body>
</html>
