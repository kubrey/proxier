# Proxier

Simple proxy parser

### Usage

```
require_once('vendor/autoload.php');

use proxier/ProxySearcher;

$proxy = new ProxySearcher();
$proxies = $proxy->run();
var_dump($proxies);

```

`$proxies` contains an array of proxy addresses from each parsed site
