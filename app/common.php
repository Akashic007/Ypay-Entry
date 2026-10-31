<?php
// 应用公共文件

use think\App;
use ba\Filesystem;
use think\facade\View;
use think\Response;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Event;
use think\facade\Config;
use think\response\Json;
use voku\helper\AntiXSS;
use app\admin\model\Config as configModel;
use think\exception\HttpResponseException;
use Symfony\Component\HttpFoundation\IpUtils;

if (!function_exists('__')) {

    /**
     * 语言翻译
     * @param string $name 被翻译字符
     * @param array $vars 替换字符数组
     * @param string $lang 翻译语言
     * @return mixed
     */
    function __(string $name, array $vars = [], string $lang = ''): mixed
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        return Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('filter')) {

    /**
     * 输入过滤
     * 富文本反XSS请使用 clean_xss，也就不需要及不能再 filter 了
     * @param string $string 要过滤的字符串
     * @return string
     */
    function filter(string $string): string
    {
        // 去除字符串两端空格（对防代码注入有一定作用）
        $string = trim($string);

        // 过滤html和php标签
        $string = strip_tags($string);

        // 特殊字符转实体
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }
}

if (!function_exists('clean_xss')) {

    /**
     * 清理XSS
     * 通常只用于富文本，比 filter 慢
     * @param string $string
     * @return string
     */
    function clean_xss(string $string): string
    {
        $antiXss = new AntiXSS();

        // 允许 style 属性（style="list-style-image: url(javascript:alert(0))" 任然可被正确过滤）
        $antiXss->removeEvilAttributes(['style']);

        // 检查到 xss 代码之后使用 cleanXss 替换它
        $antiXss->setReplacement('cleanXss');

        return $antiXss->xss_clean($string);
    }
}

if (!function_exists('htmlspecialchars_decode_improve')) {
    /**
     * html解码增强
     * 被 filter函数 内的 htmlspecialchars 编码的字符串，需要用此函数才能完全解码
     * @param string $string
     * @param int $flags
     * @return string
     */
    function htmlspecialchars_decode_improve(string $string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401): string
    {
        return htmlspecialchars_decode($string, $flags);
    }
}

if (!function_exists('get_sys_config')) {

    /**
     * 获取站点的系统配置，不传递参数则获取所有配置项
     * @param string $name 变量名
     * @param string $group 变量分组，传递此参数来获取某个分组的所有配置项
     * @param bool $concise 是否开启简洁模式，简洁模式下，获取多项配置时只返回配置的键值对
     * @return mixed
     */
    function get_sys_config(string $name = '', string $group = '', bool $concise = true): mixed
    {
        try {
            if ($name) {
                // 直接使用->value('value')不能使用到模型的类型格式化
                $config = configModel::cache($name, null, configModel::$cacheTag)->where('name', $name)->find();
                if ($config) $config = $config['value'];
            } else {
                if ($group) {
                    $temp = configModel::cache('group' . $group, null, configModel::$cacheTag)->where('group', $group)->select()->toArray();
                } else {
                    $temp = configModel::cache('sys_config_all', null, configModel::$cacheTag)->order('weigh desc')->select()->toArray();
                }
                if ($concise) {
                    $config = [];
                    foreach ($temp as $item) {
                        $config[$item['name']] = $item['value'];
                    }
                } else {
                    $config = $temp;
                }
            }
        } catch (Exception $e) {
            return null;
        }
        return $config;
    }
}

if (!function_exists('get_route_remark')) {

    /**
     * 获取当前路由后台菜单规则的备注信息
     * @return string
     */
    function get_route_remark(): string
    {
        $controllerName = request()->controller(true);
        $actionName = request()->action(true);
        $path = str_replace('.', '/', $controllerName);

        $remark = Db::name('admin_rule')
            ->where('name', $path)
            ->whereOr('name', $path . '/' . $actionName)
            ->value('remark');
        return __((string)$remark);
    }
}

if (!function_exists('full_url')) {

    /**
     * 获取资源完整url地址；若安装了云存储或 config/buildadmin.php 配置了CdnUrl，则自动使用对应的CdnUrl
     * @param string $relativeUrl 资源相对地址 不传入则获取域名
     * @param string|bool $domain 是否携带域名 或者直接传入域名
     * @param string $default 默认值
     * @return string
     */
    function full_url(string $relativeUrl = '', string|bool $domain = true, string $default = ''): string
    {
        // 存储/上传资料配置
        Event::trigger('uploadConfigInit', App::getInstance());

        $cdnUrl = Config::get('buildadmin.cdn_url');
        if (!$cdnUrl) {
            $cdnUrl = request()->upload['cdn'] ?? '//' . request()->host();
        }

        if ($domain === true) {
            $domain = $cdnUrl;
        } elseif ($domain === false) {
            $domain = '';
        }

        $relativeUrl = $relativeUrl ?: $default;
        if (!$relativeUrl) return $domain;

        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        if (preg_match('/^http(s)?:\/\//', $relativeUrl) || preg_match($regex, $relativeUrl) || $domain === false) {
            return $relativeUrl;
        }

        $url = $domain . $relativeUrl;
        $cdnUrlParams = Config::get('buildadmin.cdn_url_params');
        if ($domain === $cdnUrl && $cdnUrlParams) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . $cdnUrlParams;
        }

        return $url;
    }
}

if (!function_exists('encrypt_password')) {

    /**
     * 加密密码
     * @deprecated 使用 hash_password 代替
     */
    function encrypt_password($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($encrypt($password) . $salt);
    }
}

if (!function_exists('hash_password')) {

    /**
     * 创建密码散列（hash）
     */
    function hash_password(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (!function_exists('verify_password')) {

    /**
     * 验证密码是否和散列值匹配
     * @param string $password 密码
     * @param string $hash 散列值
     * @param array $extend 扩展数据
     */
    function verify_password(string $password, string $hash, array $extend = []): bool
    {
        // 第一个表达式直接检查是否为 password_hash 函数创建的 hash 的典型格式，即：$algo$cost$salt.hash
        if (str_starts_with($hash, '$') || password_get_info($hash)['algoName'] != 'unknown') {
            return password_verify($password, $hash);
        } else {
            // 兼容旧版 md5 加密的密码
            return encrypt_password($password, $extend['salt'] ?? '') === $hash;
        }
    }
}

if (!function_exists('str_attr_to_array')) {

    /**
     * 将字符串属性列表转为数组
     * @param string $attr 属性，一行一个，无需引号，比如：class=input-class
     * @return array
     */
    function str_attr_to_array(string $attr): array
    {
        if (!$attr) return [];
        $attr = explode("\n", trim(str_replace("\r\n", "\n", $attr)));
        $attrTemp = [];
        foreach ($attr as $item) {
            $item = explode('=', $item);
            if (isset($item[0]) && isset($item[1])) {
                $attrVal = $item[1];
                if ($item[1] === 'false' || $item[1] === 'true') {
                    $attrVal = !($item[1] === 'false');
                } elseif (is_numeric($item[1])) {
                    $attrVal = (float)$item[1];
                }
                if (strpos($item[0], '.')) {
                    $attrKey = explode('.', $item[0]);
                    if (isset($attrKey[0]) && isset($attrKey[1])) {
                        $attrTemp[$attrKey[0]][$attrKey[1]] = $attrVal;
                        continue;
                    }
                }
                $attrTemp[$item[0]] = $attrVal;
            }
        }
        return $attrTemp;
    }
}

if (!function_exists('action_in_arr')) {

    /**
     * 检测一个方法是否在传递的数组内
     * @param array $arr
     * @return bool
     */
    function action_in_arr(array $arr = []): bool
    {
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        if (in_array(strtolower(request()->action()), $arr) || in_array('*', $arr)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('build_suffix_svg')) {

    /**
     * 构建文件后缀的svg图片
     * @param string $suffix 文件后缀
     * @param ?string $background 背景颜色，如：rgb(255,255,255)
     * @return string
     */
    function build_suffix_svg(string $suffix = 'file', string $background = null): string
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total = unpack('L', hash('adler32', $suffix, true))[1];
        $hue = $total % 360;
        [$r, $g, $b] = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ?: "rgb($r,$g,$b)";

        return '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:' . $background . ';" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">' . $suffix . '</tspan></text></g>
        </svg>';
    }
}

if (!function_exists('get_area')) {

    /**
     * 获取省份地区数据
     * @throws Throwable
     */
    function get_area(): array
    {
        $province = request()->get('province', '');
        $city = request()->get('city', '');
        $where = ['pid' => 0, 'level' => 1];
        if ($province !== '') {
            $where['pid'] = $province;
            $where['level'] = 2;
            if ($city !== '') {
                $where['pid'] = $city;
                $where['level'] = 3;
            }
        }
        return Db::name('area')
            ->where($where)
            ->field('id as value,name as label')
            ->select()
            ->toArray();
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v): array
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('ip_check')) {

    /**
     * IP检查
     * @throws Throwable
     */
    function ip_check($ip = null): void
    {
        $ip = is_null($ip) ? request()->ip() : $ip;
        $noAccess = get_sys_config('no_access_ip');
        $noAccess = !$noAccess ? [] : array_filter(explode("\n", str_replace("\r\n", "\n", $noAccess)));
        if ($noAccess && IpUtils::checkIp($ip, $noAccess)) {
            $response = Response::create(['msg' => 'No permission request'], 'json', 403);
            throw new HttpResponseException($response);
        }
    }
}

if (!function_exists('set_timezone')) {

    /**
     * 设置时区
     * @throws Throwable
     */
    function set_timezone($timezone = null): void
    {
        $defaultTimezone = Config::get('app.default_timezone');
        $timezone = is_null($timezone) ? get_sys_config('time_zone') : $timezone;
        if ($timezone && $defaultTimezone != $timezone) {
            Config::set([
                'app.default_timezone' => $timezone
            ]);
            date_default_timezone_set($timezone);
        }
    }
}

if (!function_exists('get_upload_config')) {

    /**
     * 获取上传配置
     * @return array
     */
    function get_upload_config(): array
    {
        // 存储/上传资料配置
        Event::trigger('uploadConfigInit', App::getInstance());

        $uploadConfig = Config::get('upload');
        $uploadConfig['max_size'] = Filesystem::fileUnitToByte($uploadConfig['max_size']);

        $upload = request()->upload;
        if (!$upload) {
            $uploadConfig['mode'] = 'local';
            return $uploadConfig;
        }
        unset($upload['cdn']);
        return array_merge($upload, $uploadConfig);
    }
}

if (!function_exists('get_auth_token')) {

    /**
     * 获取鉴权 token
     * @param array $names
     * @return string
     */
    function get_auth_token(array $names = ['ba', 'token']): string
    {
        $separators = [
            'header' => ['', '-'], // batoken、ba-token【ba_token 不在 header 的接受列表内因为兼容性不高，改用 http_ba_token】
            'param' => ['', '-', '_'], // batoken、ba-token、ba_token
            'server' => ['_'], // http_ba_token
        ];

        $tokens = [];
        $request = request();
        foreach ($separators as $fun => $sps) {
            foreach ($sps as $sp) {
                $tokens[] = $request->$fun(($fun == 'server' ? 'http_' : '') . implode($sp, $names));
            }
        }
        $tokens = array_filter($tokens);
        return array_values($tokens)[0] ?? '';
    }
}

if (!function_exists('keys_to_camel_case')) {

    /**
     * 将数组 key 的命名方式转换为小写驼峰
     * @param array $array 被转换的数组
     * @param array $keys 要转换的 key，默认所有
     * @return array
     */
    function keys_to_camel_case(array $array, array $keys = []): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            // 将键名转换为驼峰命名
            $camelCaseKey = $keys && in_array($key, $keys) ? parse_name($key, 1, false) : $key;

            if (is_array($value)) {
                // 如果值是数组，递归转换
                $result[$camelCaseKey] = keys_to_camel_case($value);
            } else {
                $result[$camelCaseKey] = $value;
            }
        }
        return $result;
    }
}

if (!function_exists('httpCurl')) {

    /**
     * 发送HTTP请求
     * @param string $url
     * @param string $method
     * @param array $params
     * @param array $headers
     * @param bool $json
     * @param string $referer
     * @param string $user_agent
     * @param string $encode
     * @param int $timeout
     * @param string $cookie
     * @return array
     */
    function httpCurl(string $url, string $method = 'GET', array $params = [], array $headers = [], bool $json = false, string $referer = '', string $user_agent = '', string $encode = '', int $timeout = 10, string $cookie = ''): array
    {
        $ch = curl_init();
        $method = strtoupper($method);

        // GET请求拼接参数as_migrations
        if ($method === 'GET' && !empty($params)) {
            $query = http_build_query($params);
            $url .= (!str_contains($url, '?') ? '?' : '&') . $query;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); // 把响应头和body一起拿回来
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        // 自动解压 gzip/deflate
        curl_setopt($ch, CURLOPT_ENCODING, $encode);

        // POST/PUT/PATCH/DELETE 请求体
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $hasFile = false;
            foreach ($params as $v) {
                if ($v instanceof \CURLFile) {
                    $hasFile = true;
                    break;
                }
            }
            if ($hasFile) {
                // 文件上传：multipart/form-data，由 cURL 自动设置 Content-Type 和 boundary
                $body = $params;
            } elseif ($json) {
                // 转 UTF-8；二进制（如图片）不经过 mb_convert_encoding，避免 "Unable to detect character encoding"
                $params = array_map(function ($v) {
                    if (!is_string($v)) {
                        return $v;
                    }
                    if (str_contains($v, "\0")) {
                        return $v; // 含 NUL 视为二进制，不转换
                    }
                    $c = @mb_convert_encoding($v, 'UTF-8', 'auto');
                    return ($c !== false) ? $c : $v;
                }, $params);
                $body = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
                $headers[] = 'Content-Type: application/json';
            } else {
                $body = http_build_query($params);
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $baseHeaders = [
            "Accept:*/*",
            "Accept-Encoding:gzip,deflate,br",
            "Accept-Language:zh-CN,zh;q=0.8",
            "Connection:close"
        ];
        $headers = array_merge($baseHeaders, $headers);

        // 设置 headers 请求头
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // 设置 Cookie
        if (!empty($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        // 设置referer
        if (!empty($referer)) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        // 设置user_agent
        if (!empty($user_agent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1");
        }

        // SSL 默认关闭验证，避免自签名证书报错
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 执行请求
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            curl_close($ch);
            return [
                'status' => 0,
                'headers' => [],
                'body' => null,
                'error' => $error . ' (code: ' . curl_errno($ch) . ')',
            ];
        }

        // 拆分 header 和 body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $respHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);
        // 如果 json 模式且返回是 json，自动转数组
        if ($json) {
            $decoded = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $body = $decoded;
            }
        }

        if (empty($error)) $error = '未知问题';
        return [
            'status' => $statusCode,
            'headers' => $respHeaders,
            'body' => $body,
            'error' => $error,
        ];
    }
}

if (!function_exists('getSubstr')) {
    function getSubstr($str, $leftStr, $rightStr): string
    {
        $left = strpos($str, $leftStr);
        $right = strpos($str, $rightStr, $left);
        if ($left < 0 or $right < $left) return '';
        return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
    }
}

if (!function_exists('arrayFindByField')) {
    /**
     * 在二维数组中查找指定字段等于某个值的行
     *
     * @param array $array 数据源
     * @param string $field 要匹配的字段名
     * @param string|int|array $value 要匹配的值，可以是标量或数组
     * @param bool $multi 是否返回多条（默认 false，只返回第一条）
     *
     * @return array|null
     */
    function arrayFindByField(array $array, string $field, string|int|array $value, bool $multi = false): array|null
    {
        if ($multi) {
            // 返回所有匹配项
            return array_values(array_filter($array, function ($row) use ($field, $value) {
                if (!isset($row[$field])) {
                    return false;
                }
                // 如果 value 是数组 → 判断 in_array
                if (is_array($value)) {
                    return in_array($row[$field], $value, true);
                }
                // 否则 → 判断相等
                return $row[$field] == $value;
            }));
        } else {
            // 只返回第一条匹配
            if (is_array($value)) {
                foreach ($array as $row) {
                    if (isset($row[$field]) && in_array($row[$field], $value, true)) {
                        return $row;
                    }
                }
                return null;
            } else {
                $index = array_search($value, array_column($array, $field));
                return $index !== false ? $array[$index] : null;
            }
        }
    }
}

if (!function_exists('is_https')) {
    /**
     * 判断当前请求是否为 HTTPS
     *
     * @return bool
     */
    function is_https(): bool
    {
        if (
            (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') // Apache、IIS
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) // 443端口
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') // 反向代理/负载均衡
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') // 一些代理的特殊头
        ) {
            return true;
        }
        return false;
    }
}

if (!function_exists('errorView')) {

    /**
     * 错误试图快捷方法
     * @param string $msg
     * @param string $title
     * @param int $code
     * @param array $params
     * @return string
     */
    function errorView(string $msg = "处理请求时出现了一些错误", string $title = "发生错误", int $code = 0,array $params = []): string
    {
        return View::fetch('common/error', [
            'code' => $code,
            'title' => $title,
            'msg' => $msg,
            'params' => $params
        ]);
    }
}

if (!function_exists('successView')) {

    /**
     * 成功视图快捷方法
     * @param string $msg
     * @param string $title
     * @param int $code
     * @param array $params
     * @return string
     */
    function successView(string $msg = "您的请求处理已完成", string $title = "操作成功", int $code = 1,array $params = []): string
    {
        return View::fetch('common/success', [
            'code' => $code,
            'title' => $title,
            'msg' => $msg,
            'params' => $params
        ]);
    }
}


if (!function_exists('buildQrcodeUrl')) {
    function buildQrcodeUrl(string $data): string
    {
        return request()->domain() . '/qrcode?data=' . $data;
    }
}

if (!function_exists('is_url')) {
    /**
     * 检查字符串是否是有效的URL
     * @param string $url
     * @return bool
     */
    function is_url(string $url): bool
    {
        // 首先检查是否以http://或https://开头
        if (!preg_match('/^https?:\/\//i', $url)) {
            return false;
        }

        // 使用filter_var进行更严格的验证
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}


if (!function_exists('is_base64')) {
    /**
     * 检查字符串是否是Base64编码
     * @param string $str
     * @return bool
     */
    function is_base64(string $str): bool
    {
        // Base64字符串通常包含A-Z, a-z, 0-9, +, /, =等字符
        if (!preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $str)) {
            return false;
        }

        // 尝试解码再编码，看是否一致
        $decoded = base64_decode($str, true);
        if ($decoded === false) {
            return false;
        }

        // 确保解码后的内容不是空字符串
        if (empty($decoded)) {
            return false;
        }

        // 重新编码后应该与原字符串相同
        return base64_encode($decoded) === $str;
    }
}

if (!function_exists('generate_random_string')) {
    /**
     * 生成随机字符串的通用方法
     *
     * @param int $length 字符串长度，默认32
     * @param int $caseMode 大小写模式：1=混合、2=小写、3=大写，默认1
     * @param int $charType 字符类型：1=纯数字、2=数字+字母、3=数字+字母+符号，默认2
     * @return string 生成的随机字符串
     */
    function generate_random_string(int $length = 32, int $caseMode = 1, int $charType = 2): string
    {
        // 参数验证和默认值处理
        $length = max(1, min(1000, $length)); // 限制长度范围1-1000
        $caseMode = in_array($caseMode, [1, 2, 3]) ? $caseMode : 1;
        $charType = in_array($charType, [1, 2, 3]) ? $charType : 2;

        // 定义字符池
        $charPools = [
            1 => '0123456789', // 纯数字
            2 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', // 数字+字母
            3 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+=[]{}|;:,.<>?~' // 数字+字母+符号
        ];

        $charPool = $charPools[$charType] ?? $charPools[2];
        $poolLength = strlen($charPool);

        // 如果字符池为空或长度不足，返回空字符串
        if ($poolLength === 0) {
            return '';
        }

        $result = '';

        try {
            // 生成随机字符串
            for ($i = 0; $i < $length; $i++) {
                $randomIndex = random_int(0, $poolLength - 1);
                $result .= $charPool[$randomIndex];
            }
        } catch (\Exception $e) {
            // 如果随机数生成失败，使用备用方案
            for ($i = 0; $i < $length; $i++) {
                $randomIndex = mt_rand(0, $poolLength - 1);
                $result .= $charPool[$randomIndex];
            }
        }

        // 根据大小写模式处理
        if ($caseMode === 2) {
            // 小写模式
            $result = strtolower($result);
        } elseif ($caseMode === 3) {
            // 大写模式
            $result = strtoupper($result);
        } else {
            // 混合模式（默认）- 随机大小写
            $mixedResult = '';
            for ($i = 0; $i < strlen($result); $i++) {
                $char = $result[$i];
                // 使用 ASCII 码判断是否为字母
                $ascii = ord($char);
                // 判断是否为字母：A-Z (65-90) 或 a-z (97-122)
                if (($ascii >= 65 && $ascii <= 90) || ($ascii >= 97 && $ascii <= 122)) {
                    $mixedResult .= (mt_rand(0, 1) === 1) ? strtoupper($char) : strtolower($char);
                } else {
                    $mixedResult .= $char;
                }
            }
            $result = $mixedResult;
        }

        return $result;
    }
}


if (!function_exists('isTimestamp')) {
    /**
     * 判断一个值是否时间戳
     * @param $var
     * @return bool
     */
    function isTimestamp($var): bool
    {
        if (!is_numeric($var)) {
            return false;
        }

        $timestamp = (int)$var;

        // 有效时间戳范围（1970-01-01 到 2038-01-19）
        return $timestamp > 0 && $timestamp <= 2147483647;
    }
}


if (!function_exists('fenToYuan')) {
    /**
     * 分转元
     * @param int $fen
     * @return string
     */
    function fenToYuan(int $fen = 0): string
    {
        return bcdiv((string)$fen, '100', 2);
    }
}

if (!function_exists('yuanToFen')) {
    /**
     * 元转分 - 精确转换
     * @param string $yuan 金额（元）
     * @return int
     */
    function yuanToFen(string $yuan = '0.00'): int
    {
        return (int)bcmul($yuan, '100', 0);
    }
}


if (!function_exists('array_merge_deep')) {
    /**
     * 深度合并数组，后面的值覆盖前面的
     */
    function array_merge_deep(array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                // 如果键名是数字，直接追加（用于索引数组）
                if (is_int($key)) {
                    $result[] = $value;
                } // 如果两个值都是数组，递归合并
                elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = array_merge_deep($result[$key], $value);
                } // 否则直接覆盖
                else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}

// 删除文件
if (!function_exists('delDir')) {
    function delDir($directory): bool
    {
        if (!file_exists($directory)) {
            return true; // 目录不存在，视为删除成功
        }

        if (!is_dir($directory)) {
            return unlink($directory); // 不是目录，尝试删除文件
        }

        if (!$dir_handle = @opendir($directory)) {
            return false; // 无法打开目录，返回失败
        }

        while (($filename = readdir($dir_handle)) !== false) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $subFile = $directory . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($subFile)) {
                if (!delDir($subFile)) {
                    closedir($dir_handle);
                    return false; // 递归删除失败，终止
                }
            } elseif (is_file($subFile)) {
                if (!unlink($subFile)) {
                    closedir($dir_handle);
                    return false; // 文件删除失败
                }
            }
        }

        closedir($dir_handle);
        return rmdir($directory); // 删除空目录，并返回删除结果
    }
}


if (!function_exists('copyDirectory')) {

    /**
     * 复制目录
     * @param string $source 源目录
     * @param string $destination 目标目录
     * @param bool $overwrite 是否覆盖目标文件
     * @return void
     */
    // 复制目录
    function copyDirectory(string $source, string $destination, bool $overwrite = true): void
    {
        // ===== ① 源目录校验（fail fast）=====
        if (!is_dir($source)) {
            throw new InvalidArgumentException("源目录不存在: {$source}");
        }

        if (!is_readable($source)) {
            throw new RuntimeException("源目录不可读: {$source}");
        }

        // ===== ② 目标路径冲突校验 =====
        if (file_exists($destination) && !is_dir($destination)) {
            throw new RuntimeException("目标路径已存在且不是目录: {$destination}");
        }

        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new RuntimeException("目标目录创建失败: {$destination}");
        }

        $files = scandir($source);
        if ($files === false) {
            throw new RuntimeException("无法扫描目录: {$source}");
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourceFile = $source . DIRECTORY_SEPARATOR . $file;
            $destinationFile = $destination . DIRECTORY_SEPARATOR . $file;

            // ===== ③ 子目录 =====
            if (is_dir($sourceFile)) {

                if (file_exists($destinationFile) && !is_dir($destinationFile)) {
                    throw new RuntimeException("目标路径冲突（文件/目录不一致）: {$destinationFile}");
                }

                if (!is_dir($destinationFile) && !mkdir($destinationFile, 0755, true) && !is_dir($destinationFile)) {
                    throw new RuntimeException("目标子目录创建失败: {$destinationFile}");
                }

                copyDirectory($sourceFile, $destinationFile, $overwrite);

                $perms = fileperms($sourceFile);
                if ($perms !== false) {
                    chmod($destinationFile, $perms & 0777);
                }

            } else {

                if (!$overwrite && file_exists($destinationFile)) {
                    continue;
                }

                $dir = dirname($destinationFile);
                if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                    throw new RuntimeException("目标目录创建失败: {$dir}");
                }

                if (!copy($sourceFile, $destinationFile)) {
                    throw new RuntimeException("文件复制失败: {$sourceFile} -> {$destinationFile}");
                }

                $perms = fileperms($sourceFile);
                if ($perms !== false) {
                    chmod($destinationFile, $perms & 0777);
                }
            }
        }
    }

}

if (!function_exists('generateNoticeUrl')) {
    function generateNoticeUrl($msg = "暂无消息", $status = 'error', $url = "/notification"): string
    {
        return $url . '?status=' . $status . '&message=' . $msg;
    }
}

// 身份证脱敏
if (!function_exists('idNoMasking')) {
    function idNoMasking($idNo): string
    {
        if (strlen($idNo) == 18) {
            $idNo = substr($idNo, 0, 3) . str_repeat('*', 11) . substr($idNo, -4);
        } else {
            $idNo = substr_replace($idNo, '****', 6, 4);
        }
        return $idNo;
    }
}

// 姓名脱敏
if (!function_exists('nameMasking')) {
    function nameMasking($name): string
    {
        $length = mb_strlen($name, 'UTF-8');
        if ($length > 2) {
            $firstChar = mb_substr($name, 0, 1, 'UTF-8');
            $remaining = mb_substr($name, 2, null, 'UTF-8');
            $name = $firstChar . '*' . $remaining;
        } elseif ($length == 2) {
            // 如果是两个字，只替换第二个字
            $firstChar = mb_substr($name, 0, 1, 'UTF-8');
            $name = $firstChar . '*';
        } else {
            // 如果只有一个字，完全隐藏
            $name = '*';
        }
        return $name;
    }
}


if (!function_exists('res')) {
    /**
     * 响应结果
     * @param string $msg 提示信息
     * @param mixed|null $data 数据
     * @param int $code 状态码
     * @return Json 响应结果
     *
     */
    function res(string $msg = 'ok', mixed $data = null, int $code = 1): Json
    {
        return json([
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
            'time' => time()
        ]);
    }
}

if (!function_exists('convertIsoToTimestamp')) {
    /**
     * 将 ISO 8601 时间字符串转换为指定格式
     *
     * @param string $isoTime ISO 8601 格式时间字符串
     * @param string $format 目标格式，默认为 Y-m-d H:i:s
     * @return string
     */
    function convertIsoToTimestamp(string $isoTime, string $format = 'Y-m-d H:i:s'): string
    {
        try {
            $dateTime = new DateTime($isoTime);
            return $dateTime->format($format);
        } catch (Exception $e) {
            // 转换失败时返回当前时间
            return date($format);
        }
    }
}

if (!function_exists('getEntryChannelName')) {
    /**
     * 获取进件通道名称
     * @param string $code 平台标识
     * @return string[]
     */
    function getEntryChannelName(string $code): array
    {
        $list = config('entry.channel_type');
        foreach ($list as $item) {
            if ($item['code'] === $code) {
                return $item;
            }
        }
        return [
            'name' => '未知',
            'code' => '未知'
        ];
    }
}