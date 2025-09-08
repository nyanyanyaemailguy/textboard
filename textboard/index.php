<?php
$threads_file = "threads.json";
$threads = file_exists($threads_file) ? json_decode(file_get_contents($threads_file), true) : [];

$now = time();
// スレの有効期限チェック
$threads = array_filter($threads, function($t) use ($now) {
    return count($t['posts']) < 1000 && ($now - $t['created']) < 3600;
});
$threads = array_values($threads);

// 新規スレ作成処理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["title"], $_POST["content"])) {
    $name = trim($_POST["name"]) !== "" ? $_POST["name"] : "anonymous";
    $email = trim($_POST["email"]);
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if ($title !== "" && $content !== "") {
        $user_ip = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
        $today = date("Y-m-d");
        $uid = substr(md5($user_ip . $today), 0, 8);

        $thread_id = uniqid();
        $threads[] = [
            "id" => $thread_id,
            "title" => htmlspecialchars($title, ENT_QUOTES),
            "created" => time(),
            "posts" => [[
                "name" => htmlspecialchars($name, ENT_QUOTES),
                "email" => htmlspecialchars($email, ENT_QUOTES),
                "content" => nl2br(htmlspecialchars($content, ENT_QUOTES)),
                "time" => date("Y-m-d H:i:s"),
                "id" => $uid
            ]]
        ];
        file_put_contents($threads_file, json_encode($threads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: index.php");
        exit;
    }
}

// 保存
file_put_contents($threads_file, json_encode($threads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>untitled</title></head>
<body>
<center><img src="http://404chan.gt.tc/logo.gif" width="447" height="82">

<table border="1" width="95%"><tr><td><h1>untitled</h1></td></tr></table>
<br>
<!-- 全スレッドリンク集 -->
<table border="1" width="95%">
 <tr>
  <td>
<center>
<?php foreach ($threads as $i => $thread): ?>
    <?php echo $i+1; ?>:<a href="thread.php?id=<?php echo $thread['id']; ?>">
        <?php echo $thread['title']; ?>
    </a>
<?php endforeach; ?>
</center>
  </td>
 </tr>
</table>

<!-- 各スレ表示 -->
<?php foreach ($threads as $i => $thread): ?>
<table border="1" width="95%">
 <tr>
  <td>
    <h1><a href="thread.php?id=<?php echo $thread['id']; ?>">
        <?php echo $thread['title']; ?>
    </a></h1>
    <table>
        <?php foreach ($thread['posts'] as $j => $post): ?>
            <tr>
                <td>
                    <?php
                        $display_name = $post['name'];
                        if (!empty($post['email'])) {
                            $display_name = '<a href="mailto:' . $post['email'] . '">' . $display_name . '</a>';
                        }
                    ?>
                    <?php echo $j+1; ?> : <font color="green"><?php echo $display_name; ?></font> : <?php echo $post['time']; ?> ID:<?php echo $post['id']; ?><br>
                    <?php echo $post['content']; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
  </td>
 </tr>
</table>
<br>
<?php endforeach; ?>
</center>

<center>
<table border="1" width="95%">
 <tr>
  <td>
<h2>Post a new thread</h2>
<form method="post">
    name: <input type="text" name="name"><br>
    email: <input type="text" name="email"><br>
    title: <input type="text" name="title" required><br>
    comment:<br>
    <textarea name="content" rows="4" cols="50" required></textarea><br>
    <input type="submit" value="post">
</form>
  </td>
 </tr>
</table>
</center>
</body>
</html>
