<?php
require_once __DIR__ . '/../includes/classes/db.php';
$conn = \App\Db::conn();

<?php
// cron/send_promotions.php
require_once __DIR__ . '/../includes/classes/db.php';
require_once __DIR__ . '/../includes/classes/PromotionRepository.php';
// nếu project có components.php với sendMail(), require nó:
if(file_exists(__DIR__.'/../includes/classes/components.php')){
  require_once __DIR__.'/../includes/classes/components.php';
}

$repo = new PromotionRepository($conn);

// lấy recipients (example: customers table)
$res = $conn->query("SELECT email, name FROM customers WHERE subscribed=1");
$recips = $res->fetch_all(MYSQLI_ASSOC);

$promos = $repo->getActive();

foreach($promos as $p){
  foreach($recips as $r){
    $subject = "[Ưu đãi] ".$p->title;
    $html = "<p>Xin chào ".htmlspecialchars($r['name'] ?: 'Khách').",</p>";
    $html .= "<h3>".htmlspecialchars($p->title)."</h3><p>".nl2br(htmlspecialchars($p->description))."</p>";
    if($p->coupon_code) $html .= "<p>Mã: <b>".htmlspecialchars($p->coupon_code)."</b></p>";
    // Nếu có sendMail() dùng lại, ngược lại dùng mail()
    if(function_exists('sendMail')){
      $ok = sendMail($r['email'], $subject, $html); // mong sendMail trả true/false
    } else {
      // fallback đơn giản (không HTML tương thích tốt)
      $ok = mail($r['email'], $subject, strip_tags($html), "From: no-reply@yourdomain.com\r\nContent-type: text/plain; charset=utf-8\r\n");
    }
    $status = $ok ? 'sent' : 'failed';
    $repo->logSend($p->id, $r['email'], null, 'email', $status, $ok ? 'ok' : 'error');
    usleep(150000); // throttle
  }
}
