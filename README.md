**An HTTP request library based on the psr-7**

```
composer require phprush/requests
```

```
<?php
require dirname(__FILE__) . '/vendor/autoload.php';

use PhpRush\Requests\Demo;
use PhpRush\Requests\Http;
use PhpRush\Requests\Exceptions\TimeoutException;
use PhpRush\Requests\Exceptions\cURLException;

$demo = new Demo();
var_dump($demo->say());
echo "\n";

/* GET 处理 */
$http = new Http();
$http->setUrl("https://www.baidu.com");
$http->setMethod(Http::METHOD_GET);
$http->setTimeout(1);
$http->setHeaders([
    'X-ContactAuthor' => 'phprush'
]);
$http->setOptions([
    'useragent' => 'MicroMessenger'
]);
$http->send();

var_dump($http->getResponse()->getBody());
echo "\n";

/* 超时处理 + 异常捕获 */
try {
    var_dump(Http::get("https://www.google.com", null, null, 1)->getBody());
} catch (TimeoutException $e) {
    echo "timeout: " . $e->getMessage();
}
echo "\n";

try {
    var_dump(Http::get("https://www.google.com", null, null, 1)->getBody());
} catch (cURLException $e) {
    echo "curl error: " . $e->getMessage();
}
echo "\n";

var_dump(Http::post("http://v.juhe.cn/weixin/query", [
    'pno' => 1
], null, 1));
echo "\n";
```