<?php
include 'MailSender.php';

// 允许请求IP
$allowIpArr = [
    '58.215.179.22'
];

// 检测IP
if (!in_array($_SERVER['REMOTE_ADDR'], $allowIpArr)) {
    echo '非法IP:' . $_SERVER['REMOTE_ADDR'];
    exit(0);
}

// 获取请求参数
$headers = getallheaders();
$body = json_decode(file_get_contents("php://input"), true);
// 请求密码
$password = 'www.jkdev.cn';

// 验证提交分支是否为master
if (!isset($body['ref']) || $body['ref'] !== 'refs/heads/master') {
    echo '非主分支' . $body;
    exit(0);
}

// 验证提交密码是否正确
if (!isset($body['password']) || $body['password'] !== $password) {
    echo '密码错误';
    exit(0);
}

// 验证成功，拉取代码
$path = $body['project']['path'];
$command = 'cd /var/www/html/' . $path . ' && git pull 2>&1';
$res = shell_exec($command);

// 发送邮件
$addresses = [
    $body['sender']['email'],// 将邮件发送给发送者
    $body['repository']['owner']['email']// 将邮件发送给仓库所有者
];
// 去除重复的内容
$addresses = array_unique($addresses);

try {
    // 更新说明
    $title = '部署成功通知';
    // 构造邮件内容
    $message = $body['head_commit']['message'];// 提交信息
    $datetime = date('Y-m-d H:i:s', $body['timestamp'] / 1000);// 时间
    $pusher = $body['pusher']['name'];// 提交人
    $name = $body['project']['name'];// 项目名
    $path = $body['project']['path'];// 路径
    $content = <<<HTML
<html>
<body>
    <h2>{$body['project']['name']}已部署成功</h2>
    <p>
    描述：<span style="font-size: 16px; color: cadetblue">$message</span> <br>
    时间：<span style="font-size: 16px; color: red">$datetime</span> <br>
    提交人：<span style="font-size: 16px; color: cadetblue">$pusher</span> <br>
    项目名称：<span style="font-size: 16px; color: cadetblue">$name</span> <br> 
    项目路径：<span style="font-size: 16px; color: cadetblue">$path</span>
    </p>
</body>
</html>
HTML;

    // 发送邮件
    $emailSender = (new MailSender())->obtainEmailSender($addresses, $title, $content);
    $emailSender->send();
    // 返回结果
    echo '邮件发送成功，git pull执行结果：' . $res;
} catch (\PHPMailer\PHPMailer\Exception $e) {
    echo '邮件发送失败，git pull执行结果：' . $res . '，邮件日志：' . $e;
}
