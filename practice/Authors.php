<?php

include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $stmt = $db->prepare("SELECT id, name FROM Authors");
        $stmt->execute();
        $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
    $new = array();
    $new['name'] = empty($_COOKIE['name']) ? '' : $_COOKIE['name'];
    include('assets/frontAuthors.php');
} else {
    $errors = array();
    $messages = array();
    if (!empty($_POST['addnewdate'])) {
        if (empty($_POST['name'])) {
            $errors['name1'] = 'Заполните поле "Имя автора"';
            setcookie('name', '', time() + 24 * 60 * 60);
        } else if (!preg_match('/^[\p{L}\p{M}\s.]+$/u', $_POST['name'])) {
            $errors['name2'] = 'Некорректно заполнено поле "Имя автора"';
            setcookie('name', $_POST['name'], time() + 24 * 60 * 60);
        } else {
            $name = $_POST['name'];
            $stmt = $db->prepare("INSERT INTO Authors (name) VALUES (?)");
            $stmt->execute([$name]);
            $messages['added'] = 'Автор "'.$name.'" успешно добавлен';
            setcookie('name', '', time() + 24 * 60 * 60);
        }
    } 
    foreach ($_POST as $key => $value) {
        if (preg_match('/^clear(\d+)_x$/', $key, $matches)) {
            $id = $matches[1]; 
            $stmt = $db->prepare("SELECT id FROM Books WHERE AuthorID = ?");
            $stmt->execute([$id]);
            $empty = $stmt->rowCount() === 0;
            if (!$empty) {
                $errors['delete'] = 'Поле с <b>id = '.$id.'</b> невозможно удалить, т.к. оно связанно с каталогом книг';
            } else {
                $stmt = $db->prepare("DELETE FROM Authors WHERE id = ?");
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
            $stmt = $db->prepare("SELECT name FROM Authors WHERE id = ?");
            $stmt->execute([$id]);
            $old_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $dates['name'] = $_POST['name' . $id];

            if (array_diff_assoc($dates, $old_dates[0])) {
                $stmt = $db->prepare("UPDATE Authors SET name = ? WHERE id = ?");
                $stmt->execute([$dates['name'], $id]);
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
    header('Location: Authors.php');
}