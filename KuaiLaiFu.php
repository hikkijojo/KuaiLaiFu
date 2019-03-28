<?php
header( "Content-Type:text/html; charset=utf-8" );
echo "<pre>";

// * 2.1    无卡支付 [ 下單接口 ] 使用範例
$obj = new KuaiLaiFuPayGeteWay();
print_r( $obj->getKuaiLaiFuPayResult( '20.00', 'pay2019032700001', 'testRMB20', '可為空' ) );

// * 2.2	无卡支付查询 使用範例
$obj = new KuaiLaiFuPayQuery();
print_r( $obj->getKuaiLaiFuPayQuery( '2019032700001' ) );

// * 2.4	代付
$obj = new KuaiLaiFuProxyPay();
$obj->bankAccType = 'PRIVATE_DEBIT_ACCOUNT';
$obj->bankAccName = '許功蓋';
$obj->bankAccNo = '1234567890123456';
$obj->bankBranchNo = '123456789012';
$obj->bankCode = 'SPDB';
$obj->bankName = '上海浦东发展银行XXXXXX支行';
$obj->province = 'XX省';
$obj->city = 'XX市';

$obj->amount = '1.00';
$obj->proxyPayNo = 'proxypay201903280001';
print_r( $obj->getKuaiLaiFuProxyPayResult() );


// * 2.5	代付查询
$obj = new KuaiLaiFuProxyQuery();
print_r( $obj->getKuaiLaiFuProxyQuery( 'proxypay201903270001' ) );

// * 2.6	余额查询
$obj = new KuaiLaiFuQueryBalance();
print_r( $obj->getKuaiLaiFuBalance() );


/**
 * 快來付 第三方金流類 Class KuaiLaiFu
 * @version 1.0.0
 * @author NashYang
 * @example KuaiLaiFu.php 使用範例
 * @see 快来付商户信息.txt
 * @see 交易接口文档_1.0.3(3).docx
 */
class KuaiLaiFu {

    public $isShowSignStr = FALSE;

    /**
     * @var array 處理完成回傳內容，包含結果與資料。
     */
    public $returnData = Array(
        'result' => FALSE,
        'data' => Array(),
        'message' => '',
    );

    /**
     * @var string 商店編號
     * @todo 請填入商店編號
     */
    protected $merchantNo = 'DEVV10000000';

    /**
     * @var string 密鑰，固定值
     * @todo 請填入支付密鑰
     */
    protected $privateKey = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    /**
     * @var string 頁面通知連結位置 (文件內說 暂不可用)
     * @todo 需填入通知連結
     */
    protected $returnURL = 'http://www.url.com/KuaiLaiFu/return.php';

    /**
     * @var string 後台異步通知連結位置
     * @todo 需填入後台異步通知連結位置
     */
    protected $notifyURL = 'http://www.url.com/KuaiLaiFu/notify.php';

    /**
     * @var string 快來付商店特定 host 位置
     * @todo 需填入商店 host
     */
    protected $urlHost = '192.192.192.192:8888';

    /**
     * 快來付第三方簽名法。
     * @param array $payArr 簽名字段
     * @return string MD5簽名字串
     */
    protected function getSignStr( $payArr ) {
        $sign = '';
        ksort( $payArr );
        foreach ( $payArr AS $key => $value ) {
            if ( $key === 'key' ) {
                continue;
            }
            if ( !empty( $sign ) ) {
                $sign .= '&';
            }
            $sign .= $key . '=' . $value;
            if ( $this->isShowSignStr ) {
                echo $key . ':' . $value . '<br/>';
            }
        }
        $sign .= '&key=' . $this->privateKey;
        $sign = iconv( mb_detect_encoding( $sign ), 'UTF-8', $sign );
        if ( $this->isShowSignStr ) {
            echo 'sign:' . md5( $sign ) . '<br/><br/>';
            echo $sign . '<br/><br/>';
        }
        return md5( $sign );
    }

    /**
     * HTTP 請求並取得第三方結果。
     * @param array $postArray 請求數據陣列
     * @param string $url 請求對象 URL
     * @return bool|array 請求失敗回傳 FALSE，成功則回傳 json 返回陣列。
     */
    protected function sendHttp( $postArray, $url ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST, TRUE );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $postArray ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        $output = curl_exec( $ch );
        curl_close( $ch );
        if ( $output === FALSE ) {
            return FALSE;
        } else {
            return json_decode( $output, TRUE );
        }
    }
}

/**
 * 快來付 無卡支付 Class KuaiLaiFuPayGeteWay
 */
class KuaiLaiFuPayGeteWay extends KuaiLaiFu {

    /**
     * @var string 請求 URL
     * @example $url = 'http://host:port/pay/cnp/gateway';
     */
    private $url;

    /**
     * @var string 币种 固定填入 156。
     */
    protected $currency = '156';

    /**
     * @var string 支付方式 20000:支付宝扫码，30000:微信扫码
     */
    protected $payCode = '20000';

    /**
     * @var string 下单时间
     */
    protected $requestTime;

    /**
     * 2.1	无卡支付 [ 下單接口 ]
     * @param $amout string|float 訂單金額，必須保留小數點後兩位，最低 20
     * @param $orderNo string 訂單編號
     * @param $productName string 商品名稱
     * @param string $remark 備註
     * @return array|bool
     */
    public function getKuaiLaiFuPayResult( $amout, $orderNo, $productName, $remark = ' ' ) {
        $this->requestTime = date( 'Y-m-d H:i:s' );
        $payArr = Array(
            'merchant_no' => $this->merchantNo,
            'amount' => $amout,
            'currency' => $this->currency,
            'order_no' => $orderNo,
            'pay_code' => $this->payCode,
            'request_time' => $this->requestTime,
            'product_name' => $productName,
            'return_url' => $this->returnURL,
            'pay_ip' => $_SERVER['REMOTE_ADDR'],
            'remark' => $remark,
            'notify_url' => $this->notifyURL,
        );
        $payArr['sign'] = $this->getSignStr( $payArr );
        $this->url = 'http://' . $this->urlHost . '/pay/cnp/gateway';
        return $this->sendHttp( $payArr, $this->url );
    }
}

/**
 * 快來付 代付 Class KuaiLaiFuProxyPay
 */
class KuaiLaiFuProxyPay extends KuaiLaiFu {

    /**
     * @var string 銀行卡類型，對公輸入 'PUBLIC_ACCOUNT'，對私則輸入 'PRIVATE_DEBIT_ACCOUNT'
     */
    public $bankAccType;

    /**
     * @var string 代付訂單號
     */
    public $proxyPayNo;

    /**
     * @var float|string 代付訂單金額 保留小數點後兩位。
     */
    public $amount;

    /**
     * @var string 代付 收款人姓名
     */
    public $bankAccName;

    /**
     * @var string 代付 银行卡号
     */
    public $bankAccNo;

    /**
     * @var string 代付 开户行支行行号
     */
    public $bankBranchNo;

    /**
     * @var string 代付 银行编码
     * @see 交易接口文档_1.0.3(3).docx -> 3.3 代付支持银行
     */
    public $bankCode;

    /**
     * @var string 代付 开户行支行名称
     */
    public $bankName;

    /**
     * @var string 代付 银行卡开户省份
     */
    public $province;

    /**
     * @var string 代付 银行卡开户城市
     */
    public $city;

    /**
     * @var string 代付 接入方式，填：SELFHELP
     */
    protected $accessType = 'SELFHELP';


    /**
     * @var string 請求 URL
     * @example $url = 'http://host:port/proxypay/cnp/gateway';
     */
    private $url;

    /**
     * @return array|bool
     */
    public function getKuaiLaiFuProxyPayResult() {
        $payArr = Array(
            'merchant_no' => $this->merchantNo,
            'sett_no' => $this->proxyPayNo,
            'sett_amount' => $this->amount,
            'access_type' => $this->accessType,
            'account_type' => $this->bankAccType,
            'bank_account_name' => $this->bankAccName,
            'bank_account_no' => $this->bankAccNo,
            'bank_branch_no' => $this->bankBranchNo,
            'bank_code' => $this->bankCode,
            'bank_name' => $this->bankName,
            'province' => $this->province,
            'city' => $this->city,
        );
        $payArr['sign'] = $this->getSignStr( $payArr );
        $this->url = 'http://' . $this->urlHost . '/proxypay/cnp/gateway';
        return $this->sendHttp( $payArr, $this->url );
    }
}

/**
 * 快來付 無卡支付查詢 Class KuaiLaiFuPayQuery
 */
class KuaiLaiFuPayQuery extends KuaiLaiFu {
    /**
     * @var string 請求 URL
     * @example $url = 'http://host:port/pay/query';
     */
    private $url;

    /**
     * 2.2	无卡支付查询
     * @param $orderNo string 查詢的訂單號
     * @return array|bool
     */
    public function getKuaiLaiFuPayQuery( $orderNo ) {
        $queryArr = Array(
            'merchant_no' => $this->merchantNo,
            'order_no' => $orderNo,
        );
        $queryArr['sign'] = $this->getSignStr( $queryArr );
        $this->url = 'http://' . $this->urlHost . '/pay/query';
        return $this->sendHttp( $queryArr, $this->url );
    }
}

/**
 * 快來付 代付查詢類別 Class KuaiLaiFuProxyQuery
 */
class KuaiLaiFuProxyQuery extends KuaiLaiFu {

    /**
     * @var string 請求 URL
     * @example $url = 'http://host:port/proxypay/cnp/query';
     */
    private $url;

    /**
     * 2.5	代付查询
     * @param $orderNo string 欲查詢的代付訂單號
     * @return array|bool
     */
    public function getKuaiLaiFuProxyQuery( $orderNo ) {
        $queryArr = Array(
            'merchant_no' => $this->merchantNo,
            'sett_no' => $orderNo,
        );
        $queryArr['sign'] = $this->getSignStr( $queryArr );
        $this->url = 'http://' . $this->urlHost . '/proxypay/cnp/query';
        return $this->sendHttp( $queryArr, $this->url );
    }

}

/**
 * 快來付 餘額查詢類別 Class KuaiLaiFuQueryBalance
 * @version 1.0.0
 * @author NashYang
 * @example KuaiLaiFu.php 使用範例
 * @see 快来付商户信息.txt
 * @see 交易接口文档_1.0.3(3).docx
 */
class KuaiLaiFuQueryBalance extends KuaiLaiFu {

    /**
     * @var string 請求 URL
     * @example $url = 'http://host:port/pay/queryBalance';
     */
    private $url;

    /**
     * 2.6	余额查询 [ 餘額查詢 ]
     * @return bool|string
     */
    public function getKuaiLaiFuBalance() {
        $queryBalanceArr = Array(
            'merchant_no' => $this->merchantNo,
        );
        $queryBalanceArr['sign'] = $this->getSignStr( $queryBalanceArr );
        $this->url = 'http://' . $this->urlHost . '/pay/queryBalance';
        return $this->sendHttp( $queryBalanceArr, $this->url );
    }
}

?>
