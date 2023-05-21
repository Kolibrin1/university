<?php

include('dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        $stmt = $db->prepare("SELECT id, BookID, SellerID, BuyerID, SaleDate FROM Sales");
        $params = [];

        if (!empty($_COOKIE['date'])) {
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND SaleDate = ?" : "SaleDate = ?";
            $params[] = $_COOKIE['date'];
        }

        if (!empty($_COOKIE['sellers'])) {
            $filter_seller_ids = unserialize($_COOKIE['sellers']);
            $in_values1 = implode(',', array_fill(0, count($filter_seller_ids), '?'));
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND SellerID IN ($in_values1)" : "SellerID IN ($in_values1)";
            $params = array_merge($params, $filter_seller_ids);
        }

        if (!empty($_COOKIE['buyers'])) {
            $filter_buyer_ids = unserialize($_COOKIE['buyers']);
            $in_values2 = implode(',', array_fill(0, count($filter_buyer_ids), '?'));
            $stmt_sql = isset($stmt_sql) ? $stmt_sql." AND BuyerID IN ($in_values2)" : "BuyerID IN ($in_values2)";
            $params = array_merge($params, $filter_buyer_ids);
        }

        if (isset($stmt_sql)) {
            $stmt_sql = "SELECT id, BookID, SellerID, BuyerID, SaleDate FROM Sales WHERE ".$stmt_sql;
            $stmt = $db->prepare($stmt_sql);
            $stmt->execute($params);
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt->execute();
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $db->prepare("SELECT id FROM Sellers");
            $stmt->execute();
            $s_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filter_seller_ids = [];
            foreach ($s_ids as $s_id) {
                $filter_seller_ids[] = $s_id['id'];
            }

            $stmt = $db->prepare("SELECT id FROM Buyers");
            $stmt->execute();
            $b_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filter_buyer_ids = [];
            foreach ($b_ids as $b_id) {
                $filter_buyer_ids[] = $b_id['id'];
            }
        }
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
    $new = array();
    $new['BookID'] = empty($_COOKIE['BookID']) ? '' : $_COOKIE['BookID'];
    $new['SellerID'] = empty($_COOKIE['SellerID']) ? '' : $_COOKIE['SellerID'];
    $new['BuyerID'] = empty($_COOKIE['BuyerID']) ? '' : $_COOKIE['BuyerID'];
    $new['SaleDate'] = empty($_COOKIE['SaleDate']) ? '' : $_COOKIE['SaleDate'];
    include('assets/frontSales.php');
} else {
    $errors = array();
    $messages = array();
    if (!empty($_POST['addnewdate'])) {

        if (empty($_POST['BookID'])) {
            $errors['BookID'] = 'Заполните поле "Книга"';
        } else {
            setcookie('BookID', $_POST['BookID'], time() + 24 * 60 * 60);
        }

        if (empty($_POST['SellerID'])) {
            $errors['SellerID'] = 'Заполните поле "Продавец"';
        } else {
            setcookie('SellerID', $_POST['SellerID'], time() + 24 * 60 * 60);
        }

        if (empty($_POST['BuyerID'])) {
            $errors['BuyerID'] = 'Заполните поле "Покупатель"';
        } else {
            setcookie('BuyerID', $_POST['BuyerID'], time() + 24 * 60 * 60);
        }

        if (empty($_POST['SaleDate'])) {
            $errors['SaleDate'] = 'Заполните поле "Дата продажи"';
        } else {
            setcookie('SaleDate', $_POST['SaleDate'], time() + 24 * 60 * 60);
        }

        if (empty($errors)) {
            $BookID = $_POST['BookID'];
            $SellerID = $_POST['SellerID'];
            $BuyerID = $_POST['BuyerID'];
            $SaleDate = $_POST['SaleDate'];

            $stmt = $db->prepare("INSERT INTO Sales (BookID, SellerID, BuyerID, SaleDate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$BookID, $SellerID, $BuyerID, $SaleDate]);
            $messages['added'] = 'Данные успешно добавлены';
            setcookie('BookID', '', time() + 24 * 60 * 60);
            setcookie('SellerID', '', time() + 24 * 60 * 60);
            setcookie('BuyerID', '', time() + 24 * 60 * 60);
            setcookie('SaleDate', '', time() + 24 * 60 * 60);
        }
    } 
    foreach ($_POST as $key => $value) {
        if (preg_match('/^clear(\d+)_x$/', $key, $matches)) {
            $id = $matches[1]; 
            $stmt = $db->prepare("DELETE FROM Sales WHERE id = ?");
            $stmt->execute([$id]);
            $messages['deleted'] = 'Запись с <b>id = '.$id.'</b> успешно удалена';
        }
        if (preg_match('/^edit(\d+)_x$/', $key, $matches)) {
            $id = $matches[1];
            setcookie('edit', $id, time() + 24 * 60 * 60);
        }
        if (preg_match('/^save(\d+)_x$/', $key, $matches)) {
            setcookie('edit', '', time() + 24 * 60 * 60);
            $id = $matches[1];
            $stmt = $db->prepare("SELECT BookID, SellerID, BuyerID, SaleDate FROM Sales WHERE id = ?");
            $stmt->execute([$id]);
            $old_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $dates['BookID'] = $_POST['BookID' . $id];
            $dates['SellerID'] = $_POST['SellerID' . $id];
            $dates['BuyerID'] = $_POST['BuyerID' . $id];
            $dates['SaleDate'] = $_POST['SaleDate' . $id];

            if (array_diff_assoc($dates, $old_dates[0])) {
                $stmt = $db->prepare("UPDATE Sales SET BookID = ?, SellerID = ?, BuyerID = ?, SaleDate = ? WHERE id = ?");
                $stmt->execute([$dates['BookID'], $dates['SellerID'], $dates['BuyerID'], $dates['SaleDate'], $id]);
                $messages['edited'] = 'Запись с <b>id = '.$id.'</b> успешно обновлена';
            }
        }
    }

    if (!empty($_POST['resetall'])) {
        setcookie('date', '');
        setcookie('sellers', '');
        setcookie('buyers', '');
    }

    if (!empty($_POST['filter'])) {

        if (!empty($_POST['date']))
            setcookie('date', $_POST['date']);

        $filter_seller_ids = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'filter_seller_') !== false) {
                $id = substr($key, 14);
                $filter_seller_ids[] = $id;
            }
        }
        setcookie('sellers', serialize($filter_seller_ids));

        $filter_buyer_ids = array();
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'filter_buyer_') !== false) {
                $id = substr($key, 13);
                $filter_buyer_ids[] = $id;
            }
        }
        setcookie('buyers', serialize($filter_buyer_ids));
        
    }

    if (!empty($messages)) {
        setcookie('messages', serialize($messages), time() + 24 * 60 * 60);
    }
    if (!empty($errors)) {
        setcookie('errors', serialize($errors), time() + 24 * 60 * 60);
    }
    header('Location: Sales.php');
}