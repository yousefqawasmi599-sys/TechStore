<?php
// Run with XAMPP PHP while the local server listens on 127.0.0.1:8000.
$settings = json_decode(file_get_contents(dirname(__DIR__) . '/.runtime/admin.json'), true, 512, JSON_THROW_ON_ERROR);
$pdo = new PDO("mysql:host={$settings['host']};port={$settings['port']};dbname={$settings['name']};charset=utf8mb4", $settings['username'], $settings['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$jar = tempnam(sys_get_temp_dir(), 'store-test-');
$tag = bin2hex(random_bytes(6));
$email = "test-$tag@example.invalid";
$adminEmail = "admin-test-$tag@example.invalid";
$password = bin2hex(random_bytes(12));
$orderId = null;
$productId = null;
$passed = 0;
function check($condition, $message) {
    global $passed;
    if (!$condition) throw new RuntimeException($message);
    $passed++;
    echo "PASS $message\n";
}
function request($path, $data = null) {
    global $jar;
    $curl = curl_init('http://127.0.0.1:8000/' . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => $jar, CURLOPT_COOKIEJAR => $jar, CURLOPT_TIMEOUT => 15]);
    if ($data !== null) curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($data)]);
    $body = curl_exec($curl);
    if ($body === false) throw new RuntimeException(curl_error($curl));
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $redirect = curl_getinfo($curl, CURLINFO_REDIRECT_URL);
    curl_close($curl);
    if (preg_match('/Fatal error|Warning:|SQLSTATE|Notice:/i', $body)) throw new RuntimeException("PHP error on $path: " . strip_tags($body));
    return [$status,$body,$redirect];
}
function token() {
    [, $html] = request('login.php');
    if (!preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $html, $match)) throw new RuntimeException('CSRF token missing');
    return $match[1];
}
try {
    foreach (['index.php','shop.php','shop.php?show=offers','shop.php?search=phone','shop.php?brand_id=1&sort=price_asc','shop.php?brand_id=1&show=offers&search=iPhone&sort=price_asc','cart.php','login.php','register.php'] as $path) check(request($path)[0] === 200, "public page $path");
    check(request('shop.php?brand_id=invalid')[0]===400,'invalid brand rejected');
    check(request('shop.php?search%5B%5D=x')[0]===400,'array input rejected');
    foreach (['dashboard.php','products.php','orders.php','users.php','add_product.php','edit_product.php?id=1','order_details.php?id=1','delete_product.php?id=1','delete_user.php?id=1'] as $path) check(request('admin/'.$path)[0] === 403, "anonymous blocked: $path");
    check(request('create_admin.php')[0] === 404, 'public admin creation disabled');
    check(request('add_to_cart.php', ['product_id'=>1])[0] === 403, 'missing CSRF rejected');
    $csrf = token();
    check(request('register.php', ['csrf_token'=>$csrf,'name'=>'Test <script>alert(1)</script>','email'=>$email,'password'=>$password])[0] === 302, 'registration redirects before output');
    check(request('register.php', ['csrf_token'=>$csrf,'name'=>'Duplicate','email'=>$email,'password'=>$password])[0] === 409, 'duplicate email handled');
    check(!str_contains(request('index.php')[1], '<script>alert(1)</script>'), 'customer name escaped');
    check(request('admin/dashboard.php')[0] === 403, 'customer blocked from administration');
    $product = $pdo->query('SELECT id,price FROM products ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    check(request('product.php?id='.$product['id'])[0]===200,'product detail page');
    check(request('product.php?id=2147483647')[0]===404,'missing product returns 404');
    check(request('product.php?id=invalid')[0]===400,'invalid product detail id');
    check(request('add_to_cart.php', ['csrf_token'=>str_repeat('0',64),'product_id'=>$product['id']])[0]===403,'forged CSRF rejected');
    check(request('add_to_cart.php', ['csrf_token'=>$csrf,'product_id'=>'1) OR 1=1 --'])[0] === 400, 'SQL payload product id rejected');
    check(request('add_to_cart.php', ['csrf_token'=>$csrf,'product_id'=>$product['id']])[1] === 'success', 'add product to cart');
    check(request('add_to_cart.php')[0] === 405, 'GET cannot change cart');
    check(request('update_cart.php', ['csrf_token'=>$csrf,'id'=>$product['id'],'action'=>'increase'])[0] === 302, 'increase quantity');
    check(request('update_cart.php', ['csrf_token'=>$csrf,'id'=>$product['id'],'action'=>'decrease'])[0] === 302, 'decrease quantity');
    request('add_to_cart.php',['csrf_token'=>$csrf,'product_id'=>$product['id']]);
    check(request('checkout.php')[0] === 200, 'checkout renders');
    check(request('place_order.php', ['csrf_token'=>$csrf,'name'=>'Test '.$tag,'phone'=>'bad','address'=>'Test'])[0] === 400, 'invalid checkout rejected');
    [$status,,$redirect] = request('place_order.php', ['csrf_token'=>$csrf,'name'=>'Test '.$tag,'phone'=>'+971501234567','address'=>'Test address','total_amount'=>'0.01']);
    if (preg_match('/order_id=(\d+)/',$redirect,$match)) $orderId=(int)$match[1];
    check($status===302 && $orderId, 'order saved and redirected');
    $stmt=$pdo->prepare('SELECT total_amount FROM orders WHERE id=?'); $stmt->execute([$orderId]);
    check((float)$stmt->fetchColumn() === (float)$product['price']*2, 'server computes order amount');
    $stmt=$pdo->prepare('SELECT quantity FROM order_items WHERE order_id=?'); $stmt->execute([$orderId]);
    check((int)$stmt->fetchColumn() === 2, 'order details saved');
    check(request('thank_you.php?order_id='.$orderId)[0]===200,'order confirmation');
    check(request('checkout.php')[0]===302,'cart cleared after successful order');
    check(request('place_order.php', ['csrf_token'=>$csrf,'name'=>'Test '.$tag,'phone'=>'+971501234567','address'=>'Test address'])[0]===400,'duplicate order submission rejected');
    check(request('logout.php',['csrf_token'=>$csrf])[0]===302,'logout');
    $csrf=token();
    check(request('login.php',['csrf_token'=>$csrf,'email'=>$email,'password'=>$password])[0]===302,'customer login');
    request('logout.php',['csrf_token'=>$csrf]);
    check(request('thank_you.php?order_id='.$orderId)[0]===404,'other session cannot view confirmation');
    $stmt=$pdo->prepare("INSERT INTO users(name,email,password,user_type,role) VALUES ('Temporary test admin',?,?,'admin','admin')");
    $stmt->execute([$adminEmail,password_hash($password,PASSWORD_DEFAULT)]);
    $csrf=token();
    check(request('login.php',['csrf_token'=>$csrf,'email'=>$adminEmail,'password'=>$password])[0]===302,'admin login');
    foreach (['dashboard.php','products.php','orders.php','users.php','add_product.php','edit_product.php?id='.$product['id'],'order_details.php?id='.$orderId] as $path) check(request('admin/'.$path)[0]===200,"admin page $path");
    $data=['csrf_token'=>$csrf,'name'=>'Temporary '.$tag,'brand_id'=>1,'price'=>'100.00','old_price'=>'120.00','image_url'=>'https://example.com/test.png','ram'=>'8GB','storage'=>'128GB','description'=>'Test','screen_size'=>'6.7 inches','battery'=>'5000mAh'];
    $invalid=$data; $invalid['price']='-1';
    check(request('admin/add_product.php',$invalid)[0]===400,'negative product price rejected');
    check(request('admin/add_product.php',$data)[0]===302,'admin adds product');
    $stmt=$pdo->prepare('SELECT id FROM products WHERE name=?'); $stmt->execute([$data['name']]); $productId=(int)$stmt->fetchColumn();
    check($productId>0,'new product persisted');
    $data['price']='90.00';
    check(request('admin/edit_product.php?id='.$productId,$data)[0]===302,'admin edits product');
    $stmt=$pdo->prepare('SELECT description,price,screen_size,battery FROM products WHERE id=?'); $stmt->execute([$productId]); $saved=$stmt->fetch(PDO::FETCH_ASSOC);
    check($saved['description']==='Test' && (float)$saved['price']===90.0 && $saved['screen_size']==='6.7 inches' && $saved['battery']==='5000mAh','edited product fields persisted');
    check(request('admin/delete_product.php?id='.$productId)[0]===405,'GET cannot delete product');
    check(request('admin/delete_product.php',['csrf_token'=>$csrf,'id'=>$productId])[0]===302,'admin deletes product');
    check(request('admin/delete_product.php',['csrf_token'=>$csrf,'id'=>$product['id']])[0]===409,'ordered product cannot be deleted');
    check(request('admin/order_details.php?id='.$orderId,['csrf_token'=>$csrf,'update_status'=>'1','status'=>'invented'])[0]===400,'invalid order status rejected');
    check(request('admin/order_details.php?id='.$orderId,['csrf_token'=>$csrf,'update_status'=>'1','status'=>'processing'])[0]===302,'admin updates order status');
    $app=json_decode(file_get_contents(dirname(__DIR__).'/.runtime/database.json'),true,512,JSON_THROW_ON_ERROR);
    $limited=new PDO("mysql:host={$app['host']};port={$app['port']};dbname={$app['name']};charset=utf8mb4",$app['username'],$app['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    try { $limited->exec('CREATE TABLE privilege_test_'.$tag.' (id INT)'); throw new RuntimeException('Application unexpectedly has CREATE permission'); }
    catch (PDOException $error) { check((int)$error->errorInfo[1]===1142,'application cannot create tables'); }
    try { new PDO("mysql:host={$app['host']};port={$app['port']};dbname={$app['name']}",$app['username'],'incorrect-password'); throw new RuntimeException('Database accepted a wrong password'); }
    catch (PDOException $error) { check((int)$error->errorInfo[1]===1045,'database authentication enforced'); }
    $stmt=$pdo->prepare('SELECT id FROM users WHERE email=?'); $stmt->execute([$adminEmail]); $adminId=$stmt->fetchColumn();
    request('admin/delete_user.php',['csrf_token'=>$csrf,'id'=>$adminId]);
    $stmt->execute([$adminEmail]); check((bool)$stmt->fetchColumn(),'admin accounts protected from deletion');
    $stmt=$pdo->prepare('UPDATE users SET user_type=\'user\' WHERE email=?'); $stmt->execute([$adminEmail]);
    check(request('admin/dashboard.php')[0]===403,'revoked admin permission takes effect immediately');
    request('logout.php',['csrf_token'=>$csrf]); $csrf=token();
    for($i=0;$i<5;$i++) request('login.php',['csrf_token'=>$csrf,'email'=>'limit-'.$email,'password'=>'wrong']);
    check(request('login.php',['csrf_token'=>$csrf,'email'=>'limit-'.$email,'password'=>'wrong'])[0]===429,'repeated login attempts throttled');
    echo "All $passed checks passed.\n";
} finally {
    if ($orderId) { $stmt=$pdo->prepare('DELETE FROM orders WHERE id=?'); $stmt->execute([$orderId]); }
    if ($productId) { $stmt=$pdo->prepare('DELETE FROM products WHERE id=?'); $stmt->execute([$productId]); }
    $stmt=$pdo->prepare('DELETE FROM users WHERE email IN (?,?)'); $stmt->execute([$email,$adminEmail]);
    $stmt=$pdo->prepare('DELETE FROM auth_attempts WHERE attempt_key IN (?,?,?)'); $stmt->execute([hash('sha256',$email.'|127.0.0.1'),hash('sha256',$adminEmail.'|127.0.0.1'),hash('sha256','limit-'.$email.'|127.0.0.1')]);
    @unlink($jar);
}
