<?php
session_start();
$zon = [];
$zon['url'] = $_GET['url'] ?? '';
$zon['page'] = explode("/", $_GET['url'] ?? '');
// $zon['config'] = ZonConfig();
// $zon['user'] = getLoggedinUser();

// try {
//     $socket = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
//     $con = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
//     if ($socket) {
//         # empty code;
//     } else {
//         # error if connection is not established 
//     }
// } catch (Exeception $error) {
//     if ($error) {
//         echo 'Connection is not establish';
//     }
// }

function LoadFile($name)
{
    global $zon;
    // $theme = $zon['config']['theme'];
    $theme = "portal";    
    $path = "themes/$theme/layout/" . $name . ".phtml";
    if (file_exists($path)) {
        ob_start();
        require ($path);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    } else {
        echo 'file not exists.' . $path . '\n';
    }
}

function LoadFile2($name)
{
    global $zon;
    $theme = $zon['config']['theme'];
    $path = $name;
    if (file_exists($path)) {
        ob_start();
        require ($path);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    } else {
        echo 'file not exists.' . $path . '\n';
    }
}





if (isset($_SESSION['Loggedin'])) {
    define("IsLoggedin", true);
} else {
    define("IsLoggedin", false);
}


if (isset($_SESSION['is_admin_Loggedin'])) {
    define("IsAdmin", true);
} else {
    define("IsAdmin", false);
}



// function getGame($limit)
// {
//     global $socket;
//     if ($limit !== '') {
//         $sql = $socket->query("SELECT * FROM " . T_ZON_GAMES . "  ORDER BY id DESC LIMIT $limit ");
//         $data = [];
//         while ($row = $sql->fetch_assoc()) {
//             $data[] = $row;
//         }
//         return $data;
//     }
// }



function tabActivation($page, $class)
{
    global $zon;

    if (isset($zon['page'][0]) && $zon['page'][0] == $page) {
        echo $class;
    }

}


// function getAd($offset, $d)
// {
//     global $socket;
//     $sql = $socket->query("SELECT * FROM " . T_ZON_ADS . " LIMIT $offset ");
//     $data = [];
//     while ($row = $sql->fetch_assoc()) {
//         $data = $row;
//     }
//     return $data[$d];
// }

// function getAdById($id, $d)
// {
//     global $socket;
//     $sql = $socket->query("SELECT * FROM " . T_ZON_ADS . " WHERE id=$id ORDER BY id DESC");
//     $data = [];
//     while ($row = $sql->fetch_assoc()) {
//         $data = $row;
//     }
//     return $data[$d];
// }

function makeSlug($v)
{
    $e = strtolower($v);
    $e = str_replace(" ", "-", $e);
    $e = urlencode($e);
    return $e;
}

function IsGame($slug) {
        $game_name = urldecode(str_replace("-", " ", $zon['page'][1]));
        $data = dataBy("SELECT * FROM zon_games WHERE game_name='$game_name'");
    
    if(count($data) > 0) {
        return true;
    } else {
        return false;
    }
}

function getTitle()
{
    global $zon;

    if ($zon['page'][0] == 'autoplay') {
        echo "AutoPlay - Play Random Games";
    } else if ($zon['page'][0] == 'games') {
        echo $zon['config']['games_title'];
    } else if ($zon['page'][0] == 'all-games') {
        echo $zon['config']['games_title'];
    } else if ($zon['page'][0] == 'popular-games' || ($zon['page'][0] == 'archive' && $zon['page'][1] == 'popular')) {
        echo $zon['config']['games_title'];
    } else if (isCategory($zon['page'][0])) { // for category
    $category_name = str_replace("-", " ", trim(urldecode($zon['page'][0])));
    $data = dataBy("SELECT * FROM zon_category WHERE name='$category_name'")[0];
        $category_title = $zon['config']['category_title'];
        $title = str_replace("[name]", $data['name'], $category_title);
        echo $title;
    } else if ($zon['page'][0] == 'blogs') {
        echo "Blogs";
    } else if ($zon['page'][0] == 'g' && count($zon['page']) == 2) {
        $play_title = $zon['config']['play_title'];
        $game_name = str_replace("-", " ", $zon['page'][1]);
        $game = dataBy("SELECT * FROM zon_games WHERE game_name='$game_name'")[0];
        $title = str_replace("[name]", $game['game_name'], $play_title);
        echo $title;
    } else if ($zon['page'][0] == 'blog') {
        $blog = blogById($zon['page'][2]);
        echo $blog['blog_title'];
    } else if ($zon['page'][0] == 'login') {
        echo "Login";
    } else if ($zon['page'][0] == 'register') {
        echo "Register";
    } else if ($zon['page'][0] == 'c') {
        $slug = str_replace("-", " ", $zon['page'][1]);
        $page = getPageBySlug(urldecode($slug));
        echo $page['title'];
    } else if ($zon['page'][0] == '') {
        echo $zon['config']['site_title'];
    } else if (isset($zon['page'][0]) && num_rows(T_ZON_USERS, "username='" . $zon['page'][0] . "'")) {
        $username = $zon['user']['username'];
        $pro_title = $zon['config']['profile_title'];
        $title = str_replace("[name]", $username, $pro_title);
        echo $title;
    } else {
        echo "404 Page Not Found";
    }

}



function redirect($path, $full = 0)
{
    global $site_url;
    $p = $path;
    if ($full == 1) {
        $p = $site_url . $path;
    } else {
        $p = $path;
        return $p;
    }

    echo "<script>window.location.href = '$p'</script>";

}



function formatNumber($num)
{
    if ($num >= 1000000) {
        return number_format($num / 1000000, 1) . 'm';
    } elseif ($num >= 1000) {
        return number_format($num / 1000, 1) . 'k';
    } else {
        return $num;
    }
}

function isCategory($name)
{
    $n = str_replace("-", " ", urldecode($name));
    if (num_rows(T_ZON_CATEGORY, "name='$n'")) {
        return true;
    } else {
        return false;
    }
}

function clearText($value)
{
    $v = str_replace(":", "", $value);
    $v = str_replace("'", "", $v);
    $v = str_replace(",", "", $v);
    $v = str_replace('"', "", $v);
    $v = str_replace(';', "", $v);
    $v = str_replace('-', "", $v);
    $v = str_replace('_', "", $v);
    return $v;
}