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
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <title>Книжный магазин</title>
    <script>
        $( function() {

            <?php
                $stmt = $db->prepare("SELECT MAX(Price) FROM Books");
                $stmt->execute();
                $max = $stmt->fetchColumn();
            ?>

            var range1 = <?php echo empty($range1)?"0":$range1?>;
            var range2 = <?php echo empty($range2)?"10000":$range2?>;

            $( "#slider-range" ).slider({
                range: true,
                min: 0,
                max: <?php echo $max . ','; ?>
                values: [ range1, range2 ],
                slide: function( event, ui ) {
                    $( "#amount" ).val(ui.values[ 0 ] + " ₽ - " + ui.values[ 1 ] + " ₽" );
                }
            });
            $( "#amount" ).val($( "#slider-range" ).slider( "values", 0 ) + " ₽ - " + $( "#slider-range" ).slider( "values", 1 ) + " ₽" );
        });

        function toggleFilter() {
            var filterBlock = document.getElementById("filter-block");
            if (filterBlock.style.display === "none") {
                filterBlock.style.display = "block";
            } else {
                filterBlock.style.display = "none";
            }
        }

        var expanded = false;
        function showCheckboxes() {
            var checkboxes = document.getElementById("checkboxes1");
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
                    <li><a class="active" href="#">Книги</a></li>
                    <li><a href="Sellers.php">Продавцы</a></li>
                    <li><a href="Buyers.php">Покупатели</a></li>
                    <li><a href="Sales.php">Регистрация продаж</a></li>
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
                <h2>Книги</h2>
            </div>
            <div class="main-content">
                <div class="top-table">
                    <div class="newdates">
                        <div class="newdates-item">
                            <label for="name">Название:</label>
                        </div>
                        <div class="newdates-item">
                            <input name="name" value="<?php print($new['name']); ?>" placeholder="название">
                        </div>
                        <div class="newdates-item">
                            <label for="AuthorID">Автор</label>
                        </div>
                        <div class="newdates-item">
                            <select name="AuthorID">
                                <?php
                                $stmt = $db->prepare("SELECT id, name FROM Authors");
                                $stmt->execute();
                                $Authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                print("<option selected disabled>выберите автора</option>");
                                foreach ($Authors as $аuthor) {
                                    if (!empty($new['AuthorID']) && ($new['AuthorID'] ==  $аuthor['id'])) {
                                        printf('<option selected value="%d">%d. %s</option>', $аuthor['id'], $аuthor['id'], $аuthor['name']);
                                    } else {
                                        printf('<option value="%d">%d. %s</option>', $аuthor['id'], $аuthor['id'], $аuthor['name']);
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="newdates-item">
                            <label for="Price">Цена</label>
                        </div>
                        <div class="newdates-item">
                            <input name="Price" type="number" placeholder="цена товара" min="100" max="10000" step="100" value=<?php print($new['Price']); ?>>
                        </div>
                        <div class="newdates-item">
                            <input type="submit" name="addnewdate" value="Добавить">
                        </div>
                    </div>
                    <div id="filter-block" style="display:none;">
                        <h3>Фильтр</h3>
                        <p>
                            <label for="amount">Цена:</label>
                            <input type="text" name="range" id="amount" readonly>
                        </p>
                        <div id="slider-range"></div>
                        </br>
                        <div class="multiselect">
                            <div class="selectBox" onclick="showCheckboxes()">
                                <select>
                                    <option>Автор</option>
                                </select>
                                <div class="overSelect"></div>
                            </div>
                            <div id="checkboxes1">
                                <?php
                                $stmt = $db->prepare("SELECT id, name FROM Authors");
                                $stmt->execute();
                                $Authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($Authors as $author) {
                                    echo    '<label for="author'.$author['id'].'">
                                    <input type="checkbox" ' . (empty($filter_author_ids) ? '' : (in_array($author['id'], $filter_author_ids) ? 'checked ' : '')) .
                                    'name="filter_author_'.$author['id'].'" id="author'.$author['id'].'">'.$author['name'].'</label>';
                                }
                                ?>
                                <button type="button" id="checkAll">Отменить всё</button>
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
                                <th>id</th>
                                <th>Название</th>
                                <th>Автор</th>
                                <th>Цена</th>
                                <th colspan=2>
                                    <button type="button" onclick="toggleFilter()">
                                        <img src="https://cdn-icons-png.flaticon.com/512/107/107799.png" alt="filters" width="20" height="20">
                                    </button>
                                </th>
                            <tr>';
                foreach ($values as $value) {
                    $stmt = $db->prepare("SELECT id, name FROM Authors");
                    $stmt->execute();
                    $Authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo    '<tr>';
                    echo        '<td>'; print($value['id']); echo '</td>';
                    echo        '<td>
                                    <input'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                                    else print(" "); echo 'name="name'.$value['id'].'" value="'.$value['name'].'">
                                </td>';
                    echo        '<td>';
                    echo            '<select'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                    else print(" "); echo 'name="AuthorID'.$value['id'].'">';
                                        foreach ($Authors as $author) {
                                            if ($author['id'] == $value['AuthorID']) {
                                                printf('<option selected value="%d">%d. %s</option>', $author['id'], $author['id'], $author['name']);
                                            } else {
                                                printf('<option value="%d">%d. %s</option>', $author['id'], $author['id'], $author['name']);
                                            }
                                        }
                    echo            '</select>';
                    echo        '</td>';
                    echo        '<td>
                                    <input'; if(empty($_COOKIE['edit']) || ($_COOKIE['edit'] != $value['id'])) print(" disabled ");
                                    else print(" "); echo 'name="Price'.$value['id'].'" value="'.$value['Price'].'">
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
    document.getElementById('checkAll').addEventListener('click', function() {
        var checkboxes = document.querySelectorAll('input[type=checkbox]');
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