<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Initialize $_SESSION['cart'] if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "cafenea";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get the product details from the database
function getProductDetails($conn, $id_produs)
{
    $sql = "SELECT id_produs, nume_produs, descriere, pret, poza FROM produs WHERE id_produs = $id_produs";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $id_produs = isset($_POST['id_produs']) ? $_POST['id_produs'] : null;

    // Get product details
    $product = getProductDetails($conn, $id_produs);

    if ($product) {
        // Add product to cart session
        $_SESSION['cart'][$id_produs] = $product;
    }
}

// Remove product from cart
if (isset($_POST['remove_from_cart'])) {
    $id_produs = isset($_POST['id_produs']) ? $_POST['id_produs'] : null;

    // Remove product from cart session if the key exists
    if (isset($_SESSION['cart'][$id_produs])) {
        unset($_SESSION['cart'][$id_produs]);
    }
}

// Calculate total price
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id_produs => $product) {
        $total_price += $product['pret'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coș Cumpărături</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('images/sebastian-schuppik-H7xTpvBjJS4-unsplash.jpg'); 
            background-size: cover; 
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .menu {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .content {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .product {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            margin-bottom: 10px;
        }

        .product img {
            max-width: 80px;
            max-height: 80px;
            margin-right: 10px;
        }

        .product button {
            background-color: #ff4646;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .logout {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        img {
            max-width: 100px;
            max-height: 100px;
        }

        .login-button {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
        }

        .corner-image {
            position: fixed;
            bottom: 10px;
            right: 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="menu">
        <h1>Cafenea</h1>
    </div>
    <div class="container">
        <div class="content">
            <h2>Coș Cumpărături</h2>
            <?php
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $id_produs => $product) {
                    echo "<div class='product'>
                            <div>
                                <img src='{$product['poza']}' alt='Product Image'>
                                <span>{$product['nume_produs']}</span>
                            </div>
                            <div>
                                <span>{$product['pret']} lei</span>
                                <form method='post'>
                                    <input type='hidden' name='id_produs' value='$id_produs'>
                                    <button type='submit' name='remove_from_cart'>Șterge</button>
                                </form>
                            </div>
                        </div>";
                }
            } else {
                echo "<p>Coșul tău e gol.</p>";
            }
            echo "<div class='total-price'>Total: $total_price lei</div>";
            ?>
        </div>

        <?php
        $criteria = ['Cafea', 'Ceai', 'Prăjitură'];
        $sql = "SELECT id_produs, nume_produs, descriere, pret, poza FROM produs WHERE categorie IN ('" . implode("','", $criteria) . "')";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<div class='content'>
                    <h2>Menu</h2>
                    <table border='1'>
                        <tr>
                            <th>Nume Produs</th>
                            <th>Descriere</th>
                            <th>Pret(lei)</th>
                            <th>Poza</th>
                            <th>Acțiune</th>
                        </tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['nume_produs']}</td>
                        <td>{$row['descriere']}</td>
                        <td>{$row['pret']}</td>
                        <td><img src='{$row['poza']}' alt='Product Image'></td>
                        <td>
                            <form method='post'>
                                <input type='hidden' name='id_produs' value='{$row['id_produs']}'>
                                <button type='submit' name='add_to_cart'>Adaugă în coș</button>
                            </form>
                        </td>
                    </tr>";
            }

            echo "</table>
                </div>";
        } else {
            echo "<p>Fără rezultate</p>";
        }

        $conn->close();
        ?>
    </div>
    <a href="main.html">
    <img class="corner-image" src="image\switch.png" alt="Main Page" width="50" height="50">
</body>

</html>