<?php
$threads_file = "threads.json";
$threads = file_exists($threads_file) ? json_decode(file_get_contents($threads_file), true) : [];

$now = time();
$threads = array_filter($threads, function($t) use ($now) {
    return count($t['posts']) < 1000 && ($now - $t['created']) < 3600;
});
$threads = array_values($threads);

$id = $_GET["id"] ?? "";
$thread_key = null;

foreach ($threads as $k => $t) {
    if ($t["id"] === $id) {
        $thread_key = $k;
        break;
    }
}

if ($thread_key === null) {
    die("The thread does not exist.");
}

// 投稿処理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["content"])) {
    $name = trim($_POST["name"]) !== "" ? $_POST["name"] : "anonymous";
    $email = trim($_POST["email"]);
    $content = trim($_POST["content"]);
    if ($content !== "") {
        $user_ip = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
        $today = date("Y-m-d");
        $uid = substr(md5($user_ip . $today), 0, 8);

        $threads[$thread_key]["posts"][] = [
            "name" => htmlspecialchars($name, ENT_QUOTES),
            "email" => htmlspecialchars($email, ENT_QUOTES),
            "content" => nl2br(htmlspecialchars($content, ENT_QUOTES)),
            "time" => date("Y-m-d H:i:s"),
            "id" => $uid
        ];
        file_put_contents($threads_file, json_encode($threads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: thread.php?id=" . urlencode($id));
        exit;
    }
}

$thread = $threads[$thread_key];
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title><?php echo $thread["title"]; ?></title></head>
<body>
<font color="red"><h1><?php echo $thread["title"]; ?></h1></font>
<?php foreach ($thread["posts"] as $i => $post): ?>
    <?php
        $display_name = $post["name"];
        if (!empty($post["email"])) {
            $display_name = '<a href="mailto:' . $post["email"] . '">' . $display_name . '</a>';
        }
    ?>
    <p><?php echo $i+1; ?> : <font color="green"><?php echo $display_name; ?></font> : <?php echo $post["time"]; ?> ID:<?php echo $post["id"]; ?><br>
    <?php echo $post["content"]; ?></p>
<?php endforeach; ?>
<br>
<hr>
<center>Reload [Ctrl+R]</center>
<hr>
<form method="post">
    <input type="submit" value="post">
    name: <input type="text" name="name">
    E-mail (optional): <input type="text" name="email"><br>
    <textarea name="content" rows="4" cols="70" required></textarea><br>
</form>
</body>
</html>
