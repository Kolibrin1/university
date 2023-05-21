<?php

include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $stmt = $db->prepare("SELECT id, name, AuthorID, Price FROM Books");
        $params = [];
        
        if (!empty($_COOKIE['range'])) {
            $range = unserialize($_COOKIE['range']);
            list($range1, $range2) = explode(' - ', $range);
            $range1 = str_replace(' ₽', '', $range1);
            $range2 = str_replace(' ₽', '', $range2);
            $stmt_sql = "Price > ? AND Price <= ?";
            $params = array_merge($params, [$range1, $range2]);
        }
        
        if (!empty($_COOKIE['authors'])) {
            $filter_author_ids = unserialize($_COOKIE['authors']);
            $in_values = implode(',', array_fill(0, count($filter_author_ids), '?'));
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND AuthorID IN ($in_values)" : "AuthorID IN ($in_values)";
            $params = array_merge($params, $filter_author_ids);
        }
        
        if (isset($stmt_sql)) {
            $stmt_sql = "SELECT id, name, AuthorID, Price FROM Books WHERE ".$stmt_sql;
            $stmt = $db->prepare($stmt_sql);
            $stmt->execute($params);
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt->execute();
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $db->prepare("SELECT id FROM Authors");
            $stmt->execute();
            $a_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filter_author_ids = [];
            foreach ($a_ids as $a_id) {
                $filter_author_ids[] = $a_id['id'];
            }
        }
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
    $new = array();
    $new['name'] = empty($_COOKIE['name']) ? '' : $_COOKIE['name'];
    $new['AuthorID'] = empty($_COOKIE['AuthorID']) ? '' : $_COOKIE['AuthorID'];
    $new['Price'] = empty($_COOKIE['Price']) ? '' : $_COOKIE['Price'];
    include('assets/frontBooks.php');
} else {
    $errors = array();
    $messages = array();
    if (!empty($_POST['addnewdate'])) {
        if (empty($_POST['name'])) {
            $errors['name1'] = 'Заполните поле "Название книги"';
            setcookie('name', '', time() + 24 * 60 * 60);
        } else if (!preg_match('/^[\p{L}\p{M}\s.]+$/u', $_POST['name'])) {
            $errors['name2'] = 'Некорректно заполнено поле "Название книги"';
            setcookie('name', $_POST['name'], time() + 24 * 60 * 60);
        } else {
            setcookie('name', $_POST['name'], time() + 24 * 60 * 60);
        }
        if (empty($_POST['AuthorID'])) {
            $errors['AuthorID'] = 'Заполните поле "AuthorID"';
        } else {
            setcookie('AuthorID', $_POST['AuthorID'], time() + 24 * 60 * 60);
        }
        if (empty($_POST['Price'])) {
            $errors['Price'] = 'Заполните поле "Price"';
            setcookie('Price', '', time() + 24 * 60 * 60);
        } else {
            setcookie('Price', $_POST['Price'], time() + 24 * 60 * 60);
        }
        if (empty($errors)) {
            $name = $_POST['name'];
            $AuthorID = $_POST['AuthorID'];
            $Price = $_POST['Price'];
            $stmt = $db->prepare("INSERT INTO Books (name, AuthorID, Price) VALUES (?, ?, ?)");
            $stmt->execute([$name, $AuthorID, $Price]);
            $messages['added'] = 'Книга "'.$name.'" успешно добавлена';
            setcookie('name', '', time() + 24 * 60 * 60);
            setcookie('AuthorID', '', time() + 24 * 60 * 60);
            setcookie('Price', '', time() + 24 * 60 * 60);
        }
    } 
    foreach ($_POST as $key => $value) {
        if (preg_match('/^clear(\d+)_x$/', $key, $matches)) {
            $id = $matches[1]; 
            $stmt = $db->prepare("SELECT id FROM Sales WHERE BookID = ?");
            $stmt->execute([$id]);
            $empty = $stmt->rowCount() === 0;
            if (!$empty) {
                $errors['delete'] = 'Поле с <b>id = '.$id.'</b> невозможно удалить, т.к. оно связанно с журналом продаж';
            } else {
                $stmt = $db->prepare("DELETE FROM Books WHERE id = ?");
                $stmt->execute([$id]);
                $messages['deleted'] = 'Книга с <b>id = '.$id.'</b> успешно удалена';
            }
        }
        if (preg_match('/^edit(\d+)_x$/', $key, $matches)) {
            $id = $matches[1];
            setcookie('edit', $id, time() + 24 * 60 * 60);
        }
        if (preg_match('/^save(\d+)_x$/', $key, $matches)) {
            setcookie('edit', '', time() + 24 * 60 * 60);
            $id = $matches[1];
            $stmt = $db->prepare("SELECT name, AuthorID, Price FROM Books WHERE id = ?");
            $stmt->execute([$id]);
            $old_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $dates['name'] = $_POST['name' . $id];
            $dates['AuthorID'] = $_POST['AuthorID' . $id];
            $dates['Price'] = $_POST['Price' . $id];

            if (array_diff_assoc($dates, $old_dates[0])) {
                $stmt = $db->prepare("UPDATE Books SET name = ?, AuthorID = ?, Price = ? WHERE id = ?");
                $stmt->execute([$dates['name'], $dates['AuthorID'], $dates['Price'], $id]);
                $messages['edited'] = 'Книга с <b>id = '.$id.'</b> успешно обновлена';
            }
        }
    }
    if (!empty($messages)) {
        setcookie('messages', serialize($messages), time() + 24 * 60 * 60);
    }
    if (!empty($errors)) {
        setcookie('errors', serialize($errors), time() + 24 * 60 * 60);
    }

    if (!empty($_POST['resetall'])) {
        setcookie('range', '');
        setcookie('authors', '');
    }
    if (!empty($_POST['filter'])) {
        if (!empty($_POST['range']))
            setcookie('range', serialize($_POST['range']));

        $filter_author_ids = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'filter_author_') !== false) {
                $id = substr($key, 14);
                $filter_author_ids[] = $id;
            }
        }
        setcookie('authors', serialize($filter_author_ids));
    }

    header('Location: Books.php');
}