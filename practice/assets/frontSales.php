<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link rel="stylesheet" href="styles/style.css">
    <link type="image/x-icon" href="images/logo.png" rel="shortcut icon">
    <link type="Image/x-icon" href="images/logo.png" rel="icon">
    <title>Книжный магазин</title>
    <script>
        function toggleFilter() {
            var filterBlock = document.getElementById("filter-block");
            if (filterBlock.style.display === "none") {
                filterBlock.style.display = "block";
            } else {
                filterBlock.style.display = "none";
            }
        }

        var expanded = false;
        function showCheckboxes(checkboxesId) {
            var checkboxes = document.getElementById(checkboxesId);
            if (!expanded) {
                checkboxes.style.display = "block";
                expanded = true;
            } else {
                checkboxes.style.display = "none";
                expanded = false;
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="header-items">
            <a href="index.php" class="logo">
                <img src="images/logo.png" alt="logo" width="37" height="37">
                <h1>Книжный магазин</h1>
            </a>
            <nav>
                <ul>
                    <li><a href="Authors.php">Авторы</a></li>
                    <li><a href="Books.php">Книги</a></li>
                    <li><a href="Sellers.php">Продавцы</a></li>
                    <li><a href="Buyers.php">Покупатели</a></li>
                    <li><a class="active" href="#">Регистрация продаж</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <?php
            if (!empty($_COOKIE['messages'])) {
                echo '<div class="messages">';
                $messages = unserialize($_COOKIE['messages']);
                foreach ($messages as $message) {
                    echo $message . '</br>';
                }
                echo '</div>';
                setcookie('messages', '', time() + 24 * 60 * 60);
            }
            if (!empty($_COOKIE['errors'])) {
                echo '<div class="errors">';
                $errors = unserialize($_COOKIE['errors']);
                foreach ($errors as $error) {
                    echo $error . '</br>';
                }
                echo '</div>';
                setcookie('errors', '', time() + 24 * 60 * 60);
            }
        ?>
        <form action="" method="POST">
            <div class="main-content">
                <h2>Регистрация продаж</h2>
            </div>
            <div class="main-content">
                <div class="top-table">
                    <div class="newdates">
                        <div class="newdates-item">
                            <label for="BookID">Книга</label>
                        </div>
                        <div class="newdates-item">
                            <select name="BookID">
                                <?php
                                $stmt = $db->prepare("SELECT id, name FROM Books");
                                $stmt->execute();
                                $Books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                print("<option selected disabled>выберите книгу</option>");
                                foreach ($Books as $book) {
                                    if (!empty($new['BookID']) && ($new['BookID'] ==  $book['id'])) {
                                        printf('<option selected value="%d">%d. %s</option>', $book['id'], $book['id'], $book['name']);
                                    } else {
                                        printf('<option value="%d">%d. %s</option>', $book['id'], $book['id'], $book['name']);
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="newdates-item">
                            <label for="SellerID">Продавец</label>
                        </div>
                        <div class="newdates-item">
                            <select name="SellerID">
                                <?php
                                $stmt = $db->prepare("SELECT id, name FROM Sellers");
                                $stmt->execute();
                                $Sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                print("<option selected disabled>выберите продавца</option>");
                                foreach ($Sellers as $seller) {
                                    if (!empty($new['SellerID']) && ($new['SellerID'] ==  $seller['id'])) {
                                        printf('<option selected value="%d">%d. %s</option>', $seller['id'], $seller['id'], $seller['name']);
                                    } else {
                                        printf('<option value="%d">%d. %s</option>', $seller['id'], $seller['id'], $seller['name']);
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="newdates-item">
                            <label for="BuyerID">Покупатель</label>
                        </div>
                        <div class="newdates-item">
                            <select name="BuyerID">
                                <?php
                                $stmt = $db->prepare("SELECT id, name FROM Buyers");
                                $stmt->execute();
                                $Buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                print("<option selected disabled>выберите покупателя</option>");
                                foreach ($Buyers as $buyer) {
                                    if (!empty($new['BuyerID']) && ($new['BuyerID'] ==  $buyer['id'])) {
                                        printf('<option selected value="%d">%d. %s</option>', $buyer['id'], $buyer['id'], $buyer['name']);
                                    } else {
                                        printf('<option value="%d">%d. %s</option>', $buyer['id'], $buyer['id'], $buyer['name']);
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="newdates-item">
                                <label for="SaleDate">Дата продажи</label>
                            </div>
                            <div class="newdates-item">
                                <input type="date" name="SaleDate" value=<?php print($new['SaleDate']); ?>>
                            </div>
                        <div class="newdates-item">
                            <input type="submit" name="addnewdate" value="Добавить">
                        </div>
                    </div>
                    <div id="filter-block" style="display:none;">
                        <h3>Фильтр</h3>
                        <input type="date" name="date" value="<?php echo isset($_COOKIE["date"]) ? $_COOKIE["date"] : ""?>">
                        </br></br>
                        <div class="row">

                            <div class="multiselect">
                                <div class="selectBox" onclick="showCheckboxes('checkboxes1')">
                                    <select>
                                        <option>Продавец</option>
                                    </select>
                                    <div class="overSelect"></div>
                                </div>
                                <div id="checkboxes1">
                                    <?php
                                    $stmt = $db->prepare("SELECT id, name FROM Sellers");
                                    $stmt->execute();
                                    $Sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($Sellers as $seller) {
                                        echo '<label for="seller'.$seller['id'].'"><input type="checkbox" ';
                                        echo empty($filter_seller_ids) ? "" : (in_array($seller['id'], $filter_seller_ids) ? "checked " : "");
                                        echo 'name="filter_seller_'.$seller['id'].'" id="seller'.$seller['id'].'">'.$seller['name'].'</label>';
                                    }
                                    ?>
                                    <button type="button" id="checkAll1">Отменить всё</button>
                                </div>
                            </div>

                            <div class="multiselect">
                                <div class="selectBox" onclick="showCheckboxes('checkboxes2')">
                                    <select>
                                        <option>Покупатель</option>
                                    </select>
                                    <div class="overSelect"></div>
                                </div>
                                <div id="checkboxes2">
                                    <?php
                                    $stmt = $db->prepare("SELECT id, name FROM Buyers");
                                    $stmt->execute();
                                    $Buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($Buyers as $buyer) {
                                        echo '<label for="buyer'.$buyer['id'].'"><input type="checkbox" ';
                                        echo empty($filter_buyer_ids) ? "" : (in_array($buyer['id'], $filter_buyer_ids) ? "checked " : "");
                                        echo 'name="filter_buyer_'.$buyer['id'].'" id="buyer'.$buyer['id'].'">'.$buyer['name'].'</label>';
                                    }
                                    ?>
                                    <button type="button" id="checkAll2">Отменить всё</button>
                                </div>
                            </div>

                        </div>
                        </br></br>
                        <input type="submit" name="filter" value="Применить">
                        <input type="submit" name="resetall" value="Сбросить всё">
                    </div>
                </div>
            </div>
            <div class="main-content">
            <?php
                echo    '<table class="table-mobile">
                            <tr>
                                <th>Книга</th>
                                <th>Продавец</th>
                                <th>Покупатель</th>
                                <th>Дата продажи</th>
                                <th colspan=2>
                                    <button type="button" onclick="toggleFilter()">
                                        <img src="https://cdn-icons-png.flaticon.com/512/107/107799.png" alt="filters" width="20" height="20">
                                    </button>
                                </th>
                            <tr>';
                foreach ($values as $value) {
                    echo    '<tr>';
                    echo        '<td>';
                                    $stmt = $db->prepare("SELECT id, name FROM Books");
                                    $stmt->execute();
                                    $Books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo            '<select'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                    else print(" "); echo 'name="BookID'.$value['id'].'">';
                                        foreach ($Books as $book) {
                                            if ($book['id'] == $value['BookID']) {
                                                printf('<option selected value="%d">%d. %s</option>', $book['id'], $book['id'], $book['name']);
                                            } else {
                                                printf('<option value="%d">%d. %s</option>', $book['id'], $book['id'], $book['name']);
                                            }
                                        }
                    echo            '</select>';
                    echo        '</td>';

                    echo        '<td>';
                                    $stmt = $db->prepare("SELECT id, name FROM Sellers");
                                    $stmt->execute();
                                    $Sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo            '<select'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                    else print(" "); echo 'name="SellerID'.$value['id'].'">';
                                        foreach ($Sellers as $seller) {
                                            if ($seller['id'] == $value['SellerID']) {
                                                printf('<option selected value="%d">%d. %s</option>', $seller['id'], $seller['id'], $seller['name']);
                                            } else {
                                                printf('<option value="%d">%d. %s</option>', $seller['id'], $seller['id'], $seller['name']);
                                            }
                                        }
                    echo            '</select>';
                    echo        '</td>';

                    echo        '<td>';
                                    $stmt = $db->prepare("SELECT id, name FROM Buyers");
                                    $stmt->execute();
                                    $Buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo            '<select'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                    else print(" "); echo 'name="BuyerID'.$value['id'].'">';
                                        foreach ($Buyers as $buyer) {
                                            if ($buyer['id'] == $value['BuyerID']) {
                                                printf('<option selected value="%d">%d. %s</option>', $buyer['id'], $buyer['id'], $buyer['name']);
                                            } else {
                                                printf('<option value="%d">%d. %s</option>', $buyer['id'], $buyer['id'], $buyer['name']);
                                            }
                                        }
                    echo            '</select>';
                    echo        '</td>';

                    echo        '<td> <input'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                                                else print(" "); echo 'type="date" name="SaleDate'.$value['id'].'" value="'.$value['SaleDate'].'"> 
                                </td>';

                if (empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) {
                    echo        '<td> <input name="edit'.$value['id'].'" type="image" src="https://static.thenounproject.com/png/2185844-200.png" width="20" height="20" alt="submit"/> </td>';
                    echo        '<td> <input name="clear'.$value['id'].'" type="image" src="https://cdn-icons-png.flaticon.com/512/860/860829.png" width="20" height="20" alt="submit"/> </td>';
                } else {
                    echo        '<td colspan=2> <input name="save'.$value['id'].'" type="image" src="https://cdn-icons-png.flaticon.com/512/84/84138.png" width="20" height="20" alt="submit"/> </td>';
                }
                    echo    '</tr>';
                }
                echo '</table>';
            ?>
            </div>
        </form>
    </main>
<script>
    document.getElementById('checkAll1').addEventListener('click', 
        function() {
            var checkboxes = document.querySelectorAll('#checkboxes1 input[type=checkbox]');
            if (this.innerHTML === 'Выбрать все') {
                checkboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });
                this.innerHTML = 'Отменить все';
            } else {
                checkboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
                this.innerHTML = 'Выбрать все';
            }
        });

    document.getElementById('checkAll2').addEventListener('click',
        function() {
            var checkboxes = document.querySelectorAll('#checkboxes2 input[type=checkbox]');
            if (this.innerHTML === 'Выбрать все') {
                checkboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });
                this.innerHTML = 'Отменить все';
            } else {
                checkboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
                this.innerHTML = 'Выбрать все';
            }
        });
</script>
</body>
</html>