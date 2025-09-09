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
<head><meta charset="UTF-8"><title>all</title></head>
<body>
<center><img src="http://404chan.gt.tc/logo.gif" width="447" height="82">

<h1>all</h1>
<center><hr width="80%">
<a href="#" id="showFormLink">[Start a New Thread]</a>

<div id="formContainer" style="display:none;">
    <form method="post">
        <table>
            <tr>
                <td bgcolor="eeaa88">name: </td>
                <td><input type="text" name="name"></td>
            </tr>
            <tr>
                <td bgcolor="eeaa88">e-mail: </td>
                <td><input type="text" name="email"></td>
            </tr>
            <tr>
                <td bgcolor="eeaa88">title: </td>
                <td>
                    <input type="text" name="title" required>
                    <input type="submit" value="post">
                </td>
            </tr>
            <tr>
                <td bgcolor="eeaa88" height="62">comment: </td>
                <td><textarea name="content" rows="4" cols="48" required></textarea></td>
            </tr>
        </table>
    </form>
<ul>
 <li>Let's be kind and gentle.</li>
 <li>Please refrain from posting anything that violates public order and morals.</li>
 <li>No politics.</li>
 <li>Except for /pol/ and /b/.</li>
</ul>
</div>

<script>
document.getElementById("showFormLink").addEventListener("click", function(e) {
    e.preventDefault(); // ページ遷移を防ぐ
    this.style.display = "none"; // リンクを消す
    document.getElementById("formContainer").style.display = "block"; // フォームを表示
});
</script></center>
<hr>
</center>

<!-- 各スレ表示 -->
<?php foreach ($threads as $i => $thread): ?>
    <h1><a href="thread.php?id=<?php echo $thread['id']; ?>">
        <?php echo $thread['title']; ?>
    </a></h1>
        <?php foreach ($thread['posts'] as $j => $post): ?>
                    <?php
                        $display_name = $post['name'];
                        if (!empty($post['email'])) {
                            $display_name = '<a href="mailto:' . $post['email'] . '">' . $display_name . '</a>';
                        }
                    ?>
                    <?php echo $j+1; ?> : <font color="green"><?php echo $display_name; ?></font> : <?php echo $post['time']; ?> ID:<?php echo $post['id']; ?><br>
                    <?php echo $post['content']; ?><br>
        <?php endforeach; ?>
<hr>
<br>
<?php endforeach; ?>
<center>

</body>
</html>
